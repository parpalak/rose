<?php
/**
 * @copyright 2011-2017 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose;

use S2\Rose\Entity\ResultSet;
use S2\Rose\Entity\Snippet;
use S2\Rose\Entity\SnippetLine;
use S2\Rose\Stemmer\StemmerInterface;

/**
 * Class SnippetBuilder
 */
class SnippetBuilder
{
	const LINE_SEPARATOR = "\r";

	/**
	 * @var string
	 */
	protected $highlightTemplate;

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
	 * @return $this
	 */
	public function attachSnippets(ResultSet $result, callable $callback)
	{
		$externalIds = $result->getSortedExternalIds();

		/** @var array $contentArray */
		$contentArray = $callback($externalIds);

		$result->addProfilePoint('Snippets: obtaining');

		$contentArray = $this->cleanupContent($contentArray);

		$result->addProfilePoint('Snippets: cleaning');

		$foundWords = $result->getFoundWordPositionsByExternalId();

		foreach ($contentArray as $externalId => $content) {
			$snippet = $this->buildSnippet($foundWords[$externalId], $content);
			$result->attachSnippet($externalId, $snippet);
		}

		$result->addProfilePoint('Snippets: building');

		return $this;
	}

	/**
	 * @param array $contentArray
	 *
	 * @return array
	 */
	public function cleanupContent(array $contentArray)
	{
		// Text cleanup
		$replaceFrom = array(self::LINE_SEPARATOR, 'ё', '&nbsp;', '&mdash;', '&ndash;', '&laquo;', '&raquo;');
		$replaceTo   = array('', 'е', ' ', '—', '–', '«', '»');
		foreach (array(
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
		) as $tag) {
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
	 * @param array  $foundPositionsByWords
	 * @param string $content
	 *
	 * @return Snippet
	 */
	public function buildSnippet($foundPositionsByWords, $content)
	{
		// Stems of the words found in the $id chapter
		$stems     = array();
		$fullWords = array();
		foreach ($foundPositionsByWords as $word => $positions) {
			$stemmedWord             = $this->stemmer->stemWord($word);
			$stems[]                 = $stemmedWord;
			$fullWords[$stemmedWord] = $word;
		}

		// Breaking the text into lines
		$lines = explode(self::LINE_SEPARATOR, $content);

		$textStart    = $lines[0] . (isset($lines[1]) ? ' ' . $lines[1] : '');
		$foundWordNum = count($foundPositionsByWords);
		$snippet      = new Snippet($textStart, $foundWordNum);

		if ($foundWordNum == 0) {
			return $snippet;
		}

		if ($this->highlightTemplate) {
			$snippet->setHighlightTemplate($this->highlightTemplate);
		}

		// Check the text for the query words
		// TODO: Make sure the modifier S works correct on cyrillic
		preg_match_all(
			'#(?<=[^a-zа-я]|^)(' . implode('|', $stems) . ')[a-zа-я]*#Ssui',
			$content,
			$matches,
			PREG_OFFSET_CAPTURE
		);

		$lineNum = 0;
		$lineEnd = strlen($lines[$lineNum]);

		$foundWordsInLines = $foundStemsInLines = array();
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

			$foundStemsInLines[$lineNum][$stem] = 1;
			$foundWordsInLines[$lineNum][$word] = 1;
		}

		foreach ($foundStemsInLines as $lineIndex => $foundStemsInLine) {
			$snippetLine = new SnippetLine(
				$lines[$lineIndex],
				array_keys($foundWordsInLines[$lineIndex]),
				count($foundStemsInLine)
			);
			$snippet->attachSnippetLine($lineIndex, $snippetLine);
		}

		return $snippet;
	}
}
