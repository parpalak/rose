<?php
/**
 * @copyright 2017-2024 Roman Parpalak
 * @license   MIT
 */

declare(strict_types=1);

namespace S2\Rose\Entity;

use S2\Rose\Entity\Metadata\SnippetSource;
use S2\Rose\Exception\RuntimeException;
use S2\Rose\Helper\StringHelper;

class SnippetLine
{
    private const STORE_MARKER = "\r";

    /**
     * @var string[]
     */
    protected array $foundWords;

    protected string $line;

    protected float $relevance = 0;

    protected ?string $lineWithoutMaskedFragments = null;

    /**
     * @var string[]
     */
    protected array $maskedFragments = [];
    private int $formatId;

    /**
     * @var string[]
     */
    private array $maskRegexArray = [];

    public function __construct(string $line, int $formatId, array $foundWords, float $relevance)
    {
        $this->line       = $line;
        $this->foundWords = $foundWords;
        $this->relevance  = $relevance;
        $this->formatId   = $formatId;
    }

    public static function createFromSnippetSourceWithoutFoundWords(SnippetSource $snippetSource): self
    {
        return new static($snippetSource->getText(), $snippetSource->getFormatId(), [], 0.0);
    }

    /**
     * @return float
     */
    public function getRelevance()
    {
        return $this->relevance;
    }

    /**
     * @return string[]
     */
    public function getFoundWords(): array
    {
        return $this->foundWords;
    }

    public function getLine(): string
    {
        return $this->line;
    }

    public function getFormatId(): int
    {
        return $this->formatId;
    }

    /**
     * @throws RuntimeException
     */
    public function getHighlighted(string $highlightTemplate, bool $includeFormatting): string
    {
        if (strpos($highlightTemplate, '%s') === false) {
            throw new RuntimeException('Highlight template must contain "%s" substring for sprintf() function.');
        }

        if (\count($this->foundWords) === 0) {
            $result = $this->line;
        } else {
            $line = $this->getLineWithoutMaskedFragments();

            // TODO: After implementing formatting this regex became a set of crutches.
            // One has to break the snippets into words, clear formatting, convert words to stems
            // and detect what stems has been found. Then highlight the original text based on words source offset.
            $wordPattern               = implode('|', array_map(static fn(string $word) => preg_quote($word, '#'), $this->foundWords));
            $wordPatternWithFormatting = '(?:\\\\[' . StringHelper::FORMATTING_SYMBOLS . '])*(?:' . $wordPattern . ')(?:\\\\[' . strtoupper(StringHelper::FORMATTING_SYMBOLS) . '])*';
            $replacedLine              = preg_replace_callback(
                '#(?:\\s|^|\p{P})\\K' . $wordPatternWithFormatting . '(?:\\s+(?:' . $wordPatternWithFormatting . '))*\\b#su',
                static fn($matches) => sprintf($highlightTemplate, $matches[0]),
                $line
            );

            $result = $this->restoreMaskedFragments($replacedLine);
        }

        if ($this->formatId === SnippetSource::FORMAT_INTERNAL) {
            if ($includeFormatting) {
                $result = StringHelper::convertInternalFormattingToHtml($result);
            } else {
                $result = StringHelper::clearInternalFormatting($result);
            }
        }

        return $result;
    }

    public function setMaskRegexArray(array $regexes): void
    {
        $this->maskRegexArray = $regexes;
    }

    protected function getLineWithoutMaskedFragments(): string
    {
        if ($this->lineWithoutMaskedFragments !== null) {
            return $this->lineWithoutMaskedFragments;
        }

        // Remove substrings that are not store markers
        $this->lineWithoutMaskedFragments = str_replace(self::STORE_MARKER, '', $this->line);

        $this->lineWithoutMaskedFragments = htmlspecialchars($this->lineWithoutMaskedFragments);

        foreach (array_merge($this->maskRegexArray, ['#&(?:\\#[1-9]\d{1,3}|[A-Za-z][0-9A-Za-z]+);#']) as $maskRegex) {
            $this->lineWithoutMaskedFragments = preg_replace_callback(
                $maskRegex,
                function (array $matches) {
                    $this->maskedFragments[] = $matches[0];

                    return self::STORE_MARKER;
                },
                $this->lineWithoutMaskedFragments
            );
        }

        return $this->lineWithoutMaskedFragments;
    }

    protected function restoreMaskedFragments(string $line): string
    {
        $i = 0;
        while (true) {
            $pos = strpos($line, self::STORE_MARKER);
            if ($pos === false || !isset($this->maskedFragments[$i])) {
                break;
            }

            $line = substr_replace($line, $this->maskedFragments[$i], $pos, \strlen(self::STORE_MARKER));
            $i++;
        }

        return $line;
    }
}
