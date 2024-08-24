<?php
/**
 * @copyright 2016-2024 Roman Parpalak
 * @license   MIT
 */

declare(strict_types=1);

namespace S2\Rose\Entity;

class Snippet
{
    private const SNIPPET_LINE_COUNT = 3;

    protected string $lineSeparator = '... ';

    /**
     * @var SnippetLine[]
     */
    protected array $snippetLines = [];

    /**
     * @var SnippetLine[]
     */
    protected array $introductionSnippetLines;
    protected string $textIntroduction = '';

    protected string $highlightTemplate;
    protected array $snippetMinWordPositions = [];
    protected array $snippetMaxWordPositions = [];

    public function __construct(string $highlightTemplate, SnippetLine ...$introductionSnippetLines)
    {
        $this->highlightTemplate        = $highlightTemplate;
        $this->introductionSnippetLines = $introductionSnippetLines;
    }

    public function setLineSeparator(string $lineSeparator): self
    {
        $this->lineSeparator = $lineSeparator;

        return $this;
    }

    public function attachSnippetLine(int $minWordPosition, int $maxWordPosition, SnippetLine $snippetLine): self
    {
        $this->snippetLines[]            = $snippetLine;
        $this->snippetMinWordPositions[] = $minWordPosition;
        $this->snippetMaxWordPositions[] = $maxWordPosition;

        return $this;
    }

    public function getTextIntroduction(bool $includeFormatting = false): string
    {
        $result = [];
        foreach ($this->introductionSnippetLines as $snippetLine) {
            $result[] = $snippetLine->getHighlighted($this->highlightTemplate, $includeFormatting);
        }

        return implode(' ', $result);
    }

    public function toString(bool $includeFormatting = false): ?string
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
        $slice = \array_slice($uniqueLines, 0, self::SNIPPET_LINE_COUNT, true);

        // Sort by natural position
        ksort($slice);

        $resultSnippetLines = [];
        foreach ($slice as $idx => $weight) {
            $resultSnippetLines[$idx] = $this->snippetLines[$idx];
        }

        return $this->implodeLines($resultSnippetLines, $includeFormatting);
    }

    /**
     * @param array|SnippetLine[] $snippetLines
     */
    private function implodeLines(array $snippetLines, bool $includeFormatting): string
    {
        $result              = '';
        $previousMaxPosition = -1;

        $foundStrings = [];
        foreach ($snippetLines as $index => $snippetLine) {
            $lineStr = $snippetLine->getHighlighted($this->highlightTemplate, $includeFormatting);
            $lineStr = trim($lineStr);

            // Cleaning up unbalanced quotation marks
            /** @noinspection NotOptimalRegularExpressionsInspection */
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

            if ($previousMaxPosition === -1) {
                $result = $lineStr;
            } else {
                $result .= ($previousMaxPosition + 1 === $this->snippetMinWordPositions[$index] ? ' ' : $this->lineSeparator) . $lineStr;
            }
            $previousMaxPosition = $this->snippetMaxWordPositions[$index];
        }

        if ($this->lineSeparator === '... ') {
            $result = str_replace('.... ', '... ', $result);
        }

        return $result;
    }
}
