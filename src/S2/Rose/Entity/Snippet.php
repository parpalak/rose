<?php
/**
 * @copyright 2016-2017 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

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
    protected $snippetLines = [];

    /**
     * @var array
     */
    protected $snippetLineWeights = [];

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
        $this->snippetLineWeights[$linePosition] = $snippetLine->getRelevance();

        return $this;
    }

    /**
     * @return SnippetLine[]
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
        $stat = [];
        foreach ($this->snippetLines as $index => $snippetLine) {
            $stat[$snippetLine->getLine()][$index] = $snippetLine->getRelevance();
        }

        $uniqueLines = [];
        foreach ($stat as $indexToRelevanceMap) {
            arsort($indexToRelevanceMap);
            /** @noinspection LoopWhichDoesNotLoopInspection */
            foreach ($indexToRelevanceMap as $index => $relevance) {
                // If there are duplicates, this code takes only one copy with the greatest relevance.
                $uniqueLines[$index] = $relevance;
                break;
            }
        }

        // Reverse sorting by relevance
        arsort($uniqueLines);

        // Obtaining top of meaningful lines
        $slice = array_slice($uniqueLines, 0, self::SNIPPET_LINE_COUNT, true);

        // Sort by natural position
        ksort($slice);

        $resultSnippetLines = [];
        foreach ($slice as $position => $weight) {
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

        $foundStrings = [];
        foreach ($snippetLines as $position => $snippetLine) {
            $lineStr = $snippetLine->getHighlighted($this->highlightTemplate);
            $lineStr = trim($lineStr);

            // Cleaning up unbalanced quotation marks
            $lineStr = preg_replace('#«(.*?)»#Ss', '&laquo;\\1&raquo;', $lineStr);
            $lineStr = str_replace(['&quot;', '«', '»'], ['"', ''], $lineStr);
            if (substr_count($lineStr, '"') % 2) {
                $lineStr = str_replace('"', '', $lineStr);
            }

            // Remove repeating lines
            if (isset($foundStrings[$lineStr])) {
                continue;
            }
            $foundStrings[$lineStr] = 1;

            if ($previousPosition == -1) {
                $result = $lineStr;
            } else {
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
     * @return float|int
     */
    private function calcLinesRelevance(array $snippetLines)
    {
        if (!($this->foundWordCount > 0)) {
            return 0;
        }

        $foundWords = [];
        foreach ($snippetLines as $position => $snippetLine) {
            foreach ($snippetLine->getFoundWords() as $word) {
                $foundWords[$word] = 1;
            }
        }

        return count($foundWords) * 1.0 / $this->foundWordCount;
    }
}
