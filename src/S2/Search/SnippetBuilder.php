<?php
/**
 * @copyright 2011-2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Search;

use S2\Search\Entity\Result;
use S2\Search\Entity\Snippet;
use S2\Search\Stemmer\StemmerInterface;
use S2\Search\Storage\StorageReadInterface;

/**
 * Class SnippetBuilder
 */
class SnippetBuilder
{
	/**
	 * @var StorageReadInterface
	 */
	protected $storage;

	/**
	 * @var StemmerInterface
	 */
	protected $stemmer;

	/**
	 * SnippetBuilder constructor.
	 *
	 * @param StorageReadInterface $storage
	 * @param StemmerInterface     $stemmer
	 */
	public function __construct(StorageReadInterface $storage, StemmerInterface $stemmer)
	{
		$this->storage = $storage;
		$this->stemmer = $stemmer;
	}

	/**
	 * @param Result   $result
	 * @param callable $callback
	 *
	 * @return Snippet[]
	 */
	public function getSnippets(Result $result, callable $callback)
	{
		$externalIds = array_keys($result->getWeightByExternalId());

		/** @var array $contentArray */
		$contentArray = $callback($externalIds);

		$contentArray = $this->cleanupContent($contentArray);

		// Preparing for breaking into lines
		$contentArray = preg_replace('#(?<=[\.?!;])[ \n\t]+#sS', "\r", $contentArray);
		$foundWords   = $result->getFoundWordsByExternalId();

		$snippets = [];
		foreach ($contentArray as $externalId => $content) {
			$snippet = $this->buildSnippet($foundWords, $externalId, $content);
			$snippet->setDescription($this->storage->getTocByExternalId($externalId)->getDescription());
			$snippets[$externalId] = $snippet;
		}

		return $snippets;
	}

	/**
	 * @param array $articles
	 *
	 * @return array
	 */
	private function cleanupContent(array $articles)
	{
		// Text cleanup
		$replaceFrom = ["\r", 'ё', '&nbsp;', '&mdash;', '&ndash;', '&laquo;', '&raquo;'];
		$replaceTo   = ['', 'е', ' ', '—', '–', '«', '»',];
		foreach ([
			'<br>',
			'<br />',
			'</h1>',
			'</h2>',
			'</h3>',
			'</h4>',
			'</p>',
			'</pre>',
			'</blockquote>',
			'</li>',
		] as $tag) {
			$replaceFrom[] = $tag;
			$replaceTo[]   = $tag . "\r";
		}

		$articles = str_replace($replaceFrom, $replaceTo, $articles);
		foreach ($articles as &$string) {
			$string = strip_tags($string);
		}
		unset($string);

		return $articles;
	}

	/**
	 * @param array  $foundWords
	 * @param string $externalId
	 * @param string $content
	 *
	 * @return Snippet
	 */
	private function buildSnippet($foundWords, $externalId, $content)
	{
		// Stems of the words found in the $id chapter
		$stems     = [];
		$fullWords = [];
		foreach ($foundWords[$externalId] as $word) {
			if (!$this->storage->isExcluded($word)) {
				$stemmedWord             = $this->stemmer->stemWord($word);
				$stems[]                 = $stemmedWord;
				$fullWords[$stemmedWord] = $word;
			}
		}

		// Breaking the text into lines
		$lines     = explode("\r", $content);
		$textStart = $lines[0] . (isset($lines[1]) ? ' ' . $lines[1] : '');

		if (empty($fullWords)) {
			return new Snippet('', $textStart, 0);
		}

		// Check the text for the query words
		// Modifier S works poorly on cyrillic :(
		preg_match_all('#(?<=[^a-zа-я]|^)(' . implode('|', $stems) . ')[a-zа-я]*#sui', $content, $matches, PREG_OFFSET_CAPTURE);

		$lineNum = 0;
		$lineEnd = strlen($lines[$lineNum]);

		$found_words = $found_stems_lines = $lines_weight = [];
		foreach ($matches[0] as $i => $wordInfo) {
			$word        = mb_strtolower($wordInfo[0]);
			$stem        = mb_strtolower($matches[1][$i][0]);
			$stemmedWord = $this->stemmer->stemWord($word);

			// Ignore entry if the word stem differs from needed ones
			if ($stem != $word && $stem != $stemmedWord && $stemmedWord != $fullWords[$stem]) {
				continue;
			}

			$offset = $wordInfo[1];

			while ($lineEnd < $offset && isset($lines[$lineNum + 1])) {
				$lineNum++;
				$lineEnd += 1 + strlen($lines[$lineNum]);
			}

			$found_words[$lineNum][]            = $wordInfo[0];
			$found_stems_lines[$lineNum][$stem] = 1;
			if (isset($lines_weight[$lineNum])) {
				$lines_weight[$lineNum]++;
			}
			else {
				$lines_weight[$lineNum] = 1;
			}
		}

		// Finding the best matches for the snippet
		arsort($lines_weight);

		// Small array rearrangement
		$lines_with_weight = [];
		foreach ($lines_weight as $lineNum => $weight) {
			$lines_with_weight[$weight][] = $lineNum;
		}

		$i       = 0;
		$snippet = $foundStems = [];
		foreach ($lines_with_weight as $weight => $line_num_array) {
			while (count($line_num_array)) {
				$i++;
				// We take only 3 sentences with non-zero weight
				if ($i > 3 || !$weight) {
					break 2;
				}

				// Choose the best line with the weight given
				$max       = 0;
				$max_index = -1;
				foreach ($line_num_array as $line_index => $lineNum) {
					$future_found_stems = $foundStems;
					foreach ($found_stems_lines[$lineNum] as $stem => $weight) {
						$future_found_stems[$stem] = 1;
					}

					if ($max < count($future_found_stems)) {
						$max       = count($future_found_stems);
						$max_index = $line_index;
					}
				}

				$lineNum = $line_num_array[$max_index];
				unset($line_num_array[$max_index]);

				foreach ($found_stems_lines[$lineNum] as $stem => $weight) {
					$foundStems[$stem] = 1;
				}

				// Highlighting
				$replace = [];
				foreach ($found_words[$lineNum] as $word) {
					$replace[$word] = '<i>' . $word . '</i>';
				}

				$snippet[$lineNum] = strtr(html_entity_decode($lines[$lineNum], ENT_HTML5|ENT_NOQUOTES, 'UTF-8'), $replace);
				//$snippet[$lineNum] = strtr($lines[$lineNum], $replace);
				// Cleaning up HTML entites TODO $word may be undefined
				//$snippet[$lineNum] = preg_replace('#&[^;]{0,10}(?:<i>' . preg_quote($word, '#') . '</i>[^;]{0,15})+;#ue', 'str_replace(array("<i>", "</i>"), "", "\\0")', $snippet[$lineNum]);

				// If we have found all stems, we do not need any more sentence
				if ($max == count($stems)) {
					break 2;
				}
			}
		}

		// Sort sentences in the snippet according to the text order
		$snippetStr = '';
		ksort($snippet);
		$previous_line_num = -1;
		foreach ($snippet as $lineNum => &$line) {
			// Cleaning up unbalanced quotation makrs
			$line = preg_replace('#«(.*?)»#Ss', '&laquo;\\1&raquo;', $line);
			$line = str_replace(['&quot', '«', '»'], ['"', ''], $line);
			if (substr_count($line, '"') % 2) {
				$line = str_replace('"', '', $line);
			}

			if ($previous_line_num == -1) {
				$snippetStr = $line;
			}
			else {
				$snippetStr .= ($previous_line_num + 1 == $lineNum ? ' ' : '... ') . $line;
			}
			$previous_line_num = $lineNum;
		}
		$snippetStr = str_replace('.... ', '... ', $snippetStr);

		return new Snippet($snippetStr, $textStart, count($foundStems) * 1.0 / count($stems));
	}
}
