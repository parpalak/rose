<?php declare(strict_types=1);
/**
 * @copyright 2017-2023 Roman Parpalak
 * @license   MIT
 */

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

    protected ?string $lineWithoutEntities = null;

    /**
     * @var string[]
     */
    protected array $storedEntities = [];
    private int $formatId;

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
            $line = $this->getLineWithoutEntities();

            // TODO: After implementing formatting this regex became a set of crutches.
            // One has to break the snippets into words, clear formatting, convert words to stems
            // and detect what stems has been found. Then highlight the original text based on words source offset.
            $wordPattern               = implode('|', $this->foundWords);
            $wordPatternWithFormatting = '(?:\\\\[' . StringHelper::FORMATTING_SYMBOLS . '])*(?:' . $wordPattern . ')(?:\\\\[' . strtoupper(StringHelper::FORMATTING_SYMBOLS) . '])*';
            $replacedLine              = preg_replace_callback(
                '#(?:\\s|^|\p{P})\\K' . $wordPatternWithFormatting . '(?:\\s+(?:' . $wordPatternWithFormatting . '))*\\b#su',
                static fn($matches) => sprintf($highlightTemplate, $matches[0]),
                $line
            );

            $result = $this->restoreEntities($replacedLine);
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

    protected function getLineWithoutEntities(): string
    {
        if ($this->lineWithoutEntities !== null) {
            return $this->lineWithoutEntities;
        }

        // Remove substrings that are not store markers
        $this->lineWithoutEntities = str_replace(self::STORE_MARKER, '', $this->line);

        $this->lineWithoutEntities = htmlspecialchars($this->lineWithoutEntities);

        $this->lineWithoutEntities = preg_replace_callback(
            '#&(\\#[1-9]\d{1,3}|[A-Za-z][0-9A-Za-z]+);#',
            function (array $matches) {
                $this->storedEntities[] = $matches[0];

                return self::STORE_MARKER;
            },
            $this->lineWithoutEntities
        );

        return $this->lineWithoutEntities;
    }

    protected function restoreEntities(string $line): string
    {
        $i = 0;
        while (true) {
            $pos = strpos($line, self::STORE_MARKER);
            if ($pos === false || !isset($this->storedEntities[$i])) {
                break;
            }

            $line = substr_replace($line, $this->storedEntities[$i], $pos, \strlen(self::STORE_MARKER));
            $i++;
        }

        return $line;
    }
}
