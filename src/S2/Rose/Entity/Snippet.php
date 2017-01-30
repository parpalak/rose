<?php
/**
 * @copyright 2016-2017 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

/**
 * Class Snippet
 */
class Snippet
{
	const SNIPPET_LINE_COUNT = 3;

	/**
	 * @var string
	 */
	protected $lineSeparator = '... ';

	/**
	 * @var SnippetLine[]
	 */
	protected $snippetLines = array();

	/**
	 * @var array
	 */
	protected $snippetLineWeights = array();

	/**
	 * @var string
	 */
	protected $textIntroduction = '';

	/**
	 * @var int
	 */
	protected $foundWordCount = 0;

	/**
	 * @var string
	 */
	protected $highlightTemplate;

	/**
	 * Snippet constructor.
	 *
	 * @param string $textIntroduction
	 * @param int    $foundWordNum
	 * @param string $highlightTemplate
	 */
	public function __construct($textIntroduction, $foundWordNum, $highlightTemplate)
	{
		$this->textIntroduction  = $textIntroduction;
		$this->foundWordCount    = $foundWordNum;
		$this->highlightTemplate = $highlightTemplate;
	}

	/**
	 * @param string $lineSeparator
	 *
	 * @return $this
	 */
	public function setLineSeparator($lineSeparator)
	{
		$this->lineSeparator = $lineSeparator;

		return $this;
	}

	/**
	 * @param int         $linePosition
	 * @param SnippetLine $snippetLine
	 *
	 * @return $this
	 */
	public function attachSnippetLine($linePosition, SnippetLine $snippetLine)
	{
		$this->snippetLines[$linePosition]       = $snippetLine;
		$this->snippetLineWeights[$linePosition] = $snippetLine->getStemCount();

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSnippetLines()
	{
		return $this->snippetLines;
	}

	/**
	 * @return string
	 */
	public function getTextIntroduction()
	{
		return $this->textIntroduction;
	}

	/**
	 * @param float $acceptableRelevance
	 *
	 * @return string
	 */
	public function toString($acceptableRelevance = 0.6)
	{
		$a = $this->snippetLineWeights;

		// Reverse sorting by relevance
		arsort($a);

		// Obtaining first bunch of meaningful lines
		$a = array_slice($a, 0, self::SNIPPET_LINE_COUNT, true);

		// Sort by natural position
		ksort($a);

		$resultSnippetLines = array();
		foreach ($a as $position => $weight) {
			$resultSnippetLines[$position] = $this->snippetLines[$position];
		}

		if ($this->calcLinesRelevance($resultSnippetLines) < $acceptableRelevance) {
			return null;
		}

		$snippetStr = $this->implodeLines($resultSnippetLines);

		return $snippetStr;
	}

	/**
	 * @param SnippetLine[] $snippetLines
	 *
	 * @return string
	 */
	private function implodeLines(array $snippetLines)
	{
		$result           = '';
		$previousPosition = -1;

		foreach ($snippetLines as $position => $snippetLine) {
			/** @var SnippetLine $snippetLine */
			$lineStr = $snippetLine->getHighlighted($this->highlightTemplate);

			// Cleaning up unbalanced quotation marks
			$lineStr = preg_replace('#«(.*?)»#Ss', '&laquo;\\1&raquo;', $lineStr);
			$lineStr = str_replace(array('&quot;', '«', '»'), array('"', ''), $lineStr);
			if (substr_count($lineStr, '"') % 2) {
				$lineStr = str_replace('"', '', $lineStr);
			}

			if ($previousPosition == -1) {
				$result = $lineStr;
			}
			else {
				$result .= ($previousPosition + 1 == $position ? ' ' : $this->lineSeparator) . $lineStr;
			}
			$previousPosition = $position;
		}

		if ($this->lineSeparator == '... ') {
			$result = str_replace('.... ', '... ', $result);
		}

		return $result;
	}

	/**
	 * @param SnippetLine[] $snippetLines
	 *
	 * @return string
	 */
	private function calcLinesRelevance(array $snippetLines)
	{
		if (!($this->foundWordCount > 0)) {
			return 0;
		}

		$foundWords = array();
		foreach ($snippetLines as $position => $snippetLine) {
			/** @var SnippetLine $snippetLine */
			foreach ($snippetLine->getFoundWords() as $word) {
				$foundWords[$word] = 1;
			}
		}

		return count($foundWords) * 1.0 / $this->foundWordCount;
	}
}
