<?php declare(strict_types=1);
/**
 * @copyright 2016-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

use S2\Rose\Entity\Metadata\SnippetSource;
use S2\Rose\Helper\StringHelper;

class Snippet
{
    private const SNIPPET_LINE_COUNT = 3;

    protected string $lineSeparator = '... ';

    /**
     * @var SnippetLine[]
     */
    protected array $snippetLines = [];
    protected array $snippetLineWeights = [];

    protected string $textIntroduction = '';

    protected int $foundWordCount = 0;

    protected string $highlightTemplate;
    private array $snippetMinWordPositions = [];
    private array $snippetMaxWordPositions = [];

    public function __construct(string $textIntroduction, int $foundWordNum, string $highlightTemplate)
    {
        $this->textIntroduction  = $textIntroduction;
        $this->foundWordCount    = $foundWordNum;
        $this->highlightTemplate = $highlightTemplate;
    }

    public function setLineSeparator(string $lineSeparator): self
    {
        $this->lineSeparator = $lineSeparator;

        return $this;
    }

    public function attachSnippetLine(int $minWordPosition, int $maxWordPosition, SnippetLine $snippetLine): self
    {
        $this->snippetLines[]            = $snippetLine;
        $this->snippetLineWeights[]      = $snippetLine->getRelevance();
        $this->snippetMinWordPositions[] = $minWordPosition;
        $this->snippetMaxWordPositions[] = $maxWordPosition;

        return $this;
    }

    public function getTextIntroduction(): string
    {
        return $this->textIntroduction;
    }

    public function toString(float $acceptableRelevance = 0.6, bool $includeFormatting = false): ?string
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

        if ($this->calcLinesRelevance($resultSnippetLines) < $acceptableRelevance) {
            return null;
        }

        return $this->implodeLines($resultSnippetLines, $includeFormatting);
    }

    /**
     * @param array|SnippetLine[] $snippetLines
     *
     * @return string
     */
    private function implodeLines(array $snippetLines, bool $includeFormatting): string
    {
        $result              = '';
        $previousMaxPosition = -1;

        $foundStrings = [];
        foreach ($snippetLines as $index => $snippetLine) {
            $lineStr = $snippetLine->getHighlighted($this->highlightTemplate);
            $lineStr = trim($lineStr);

            if ($snippetLine->getFormatId() === SnippetSource::FORMAT_INTERNAL) {
                if ($includeFormatting) {
                    $lineStr = StringHelper::convertInternalFormattingToHtml($lineStr);
                } else {
                    $lineStr = StringHelper::clearInternalFormatting($lineStr);
                }
            }

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

    /**
     * @param array|SnippetLine[] $snippetLines
     *
     * @return float|int
     */
    private function calcLinesRelevance(array $snippetLines)
    {
        if (!($this->foundWordCount > 0)) {
            return 0;
        }

        $foundWords = [];
        foreach ($snippetLines as $snippetLine) {
            foreach ($snippetLine->getFoundWords() as $word) {
                $foundWords[$word] = 1;
            }
        }

        return \count($foundWords) * 1.0 / $this->foundWordCount;
    }
}
