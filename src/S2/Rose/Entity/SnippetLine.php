<?php declare(strict_types=1);
/**
 * @copyright 2017-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

use S2\Rose\Exception\RuntimeException;

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
        $this->formatId = $formatId;
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
    public function getHighlighted(string $highlightTemplate): string
    {
        if (strpos($highlightTemplate, '%s') === false) {
            throw new RuntimeException('Highlight template must contain "%s" substring for sprintf() function.');
        }

        if (\count($this->foundWords) === 0) {
            return $this->line;
        }

        $line = $this->getLineWithoutEntities();

        /** @noinspection RegExpUnnecessaryNonCapturingGroup */
        $replacedLine = preg_replace_callback(
            '#\b(?:' . implode('|', $this->foundWords) . ')(?:\s+(?:' . implode('|', $this->foundWords) . '))*\b#su',
            static fn($matches) => sprintf($highlightTemplate, $matches[0]),
            $line
        );

        return $this->restoreEntities($replacedLine);
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

            $line = substr_replace($line, $this->storedEntities[$i], $pos, strlen(self::STORE_MARKER));
            $i++;
        }

        return $line;
    }
}
