<?php
/**
 * @copyright 2011-2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Search;

use S2\Search\Entity\ResultSet;
use S2\Search\Entity\Snippet;
use S2\Search\Stemmer\StemmerInterface;

/**
 * Class SnippetBuilder
 */
class SnippetBuilder
{
	const LINE_SEPARATOR = "\r";

	/**
	 * @var string
	 */
	protected $highlightTemplate = '<i>%s</i>';

	/**
	 * @var StemmerInterface
	 */
	protected $stemmer;

	/**
	 * SnippetBuilder constructor.
	 *
	 * @param StemmerInterface     $stemmer
	 */
	public function __construct(StemmerInterface $stemmer)
	{
		$this->stemmer = $stemmer;
	}

	/**
	 * @param string $highlightTemplate
	 */
	public function setHighlightTemplate($highlightTemplate)
	{
		$this->highlightTemplate = $highlightTemplate;
	}

	/**
	 * @param ResultSet $result
	 * @param callable  $callback
	 *
	 * @return Snippet[]
	 */
	public function attachSnippets(ResultSet $result, callable $callback)
	{
		$externalIds = array_keys($result->getWeightByExternalId());

		/** @var array $contentArray */
		$contentArray = $callback($externalIds);

		$result->addProfilePoint('Snippets: obtaining');

		$contentArray = $this->cleanupContent($contentArray);

		$result->addProfilePoint('Snippets: cleaning');

		$foundWords = $result->getFoundWordsByExternalId();

		foreach ($contentArray as $externalId => $content) {
			$snippet = $this->buildSnippet($foundWords[$externalId], $content);
			$result->attachSnippet($externalId, $snippet);
		}

		$result->addProfilePoint('Snippets: building');
	}

	/**
	 * @param array $contentArray
	 *
	 * @return array
	 */
	private function cleanupContent(array $contentArray)
	{
		// Text cleanup
		$replaceFrom = [self::LINE_SEPARATOR, 'ё', '&nbsp;', '&mdash;', '&ndash;', '&laquo;', '&raquo;'];
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
			$replaceTo[]   = $tag . self::LINE_SEPARATOR;
		}

		$contentArray = str_replace($replaceFrom, $replaceTo, $contentArray);
		foreach ($contentArray as &$string) {
			$string = strip_tags($string);
		}
		unset($string);

		// Preparing for breaking into lines
		$contentArray = preg_replace('#(?<=[\.?!;])[ \n\t]+#sS', self::LINE_SEPARATOR, $contentArray);

		return $contentArray;
	}

	/**
	 * @param string[] $foundWords
	 * @param string   $content
	 *
	 * @return Snippet
	 */
	private function buildSnippet($foundWords, $content)
	{
		// Stems of the words found in the $id chapter
		$stems     = [];
		$fullWords = [];
		foreach ($foundWords as $word) {
			// TODO exclude words like 'to', 'and', ...?
			$stemmedWord             = $this->stemmer->stemWord($word);
			$stems[]                 = $stemmedWord;
			$fullWords[$stemmedWord] = $word;
		}

		// Breaking the text into lines
		$lines     = explode(self::LINE_SEPARATOR, $content);
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

		$i        = 0;
		$lineNums = $foundStems = [];
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

				$lineNums[] = $lineNum;

				// If we have found all stems, we do not need any more sentence
				if ($max == count($stems)) {
					break 2;
				}
			}
		}

		$snippetArray = [];
		foreach ($lineNums as $lineNum) {
			$snippetArray[$lineNum] = $lines[$lineNum];
		}

		// Sort sentences in the snippet according to the text order
		$snippetStr = '';
		ksort($snippetArray);
		$previous_line_num = -1;
		foreach ($snippetArray as $lineNum => &$line) {
			// Highlighting
			$replace = [];
			foreach ($found_words[$lineNum] as $word) {
				$replace[$word] = $this->highlightWord($word);
			}

			$line = strtr(html_entity_decode($line, ENT_HTML5 | ENT_NOQUOTES, 'UTF-8'), $replace);
			//$snippet[$lineNum] = strtr($lines[$lineNum], $replace);
			// Cleaning up HTML entites TODO $word may be undefined
			//$snippet[$lineNum] = preg_replace('#&[^;]{0,10}(?:<i>' . preg_quote($word, '#') . '</i>[^;]{0,15})+;#ue', 'str_replace(array("<i>", "</i>"), "", "\\0")', $snippet[$lineNum]);

			// Cleaning up unbalanced quotation makrs
			$line = preg_replace('#«(.*?)»#Ss', '&laquo;\\1&raquo;', $line);
			$line = str_replace(['&quot;', '«', '»'], ['"', ''], $line);
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

	/**
	 * @param $word
	 *
	 * @return string
	 */
	private function highlightWord($word)
	{
		return sprintf($this->highlightTemplate, $word);
	}
}
