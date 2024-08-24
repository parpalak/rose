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
use S2\Rose\Stemmer\StemmerInterface;

class SnippetLine
{
    private const STORE_MARKER = "\r";

    /**
     * @var string[]
     */
    protected array $stemsFoundSomewhere;

    protected string $line;

    protected int $formatId;

    protected StemmerInterface $stemmer;

    protected float $relevance;

    protected ?string $lineWithoutMaskedFragments = null;

    /**
     * @var string[]
     */
    protected array $maskedFragments = [];

    /**
     * @var string[]
     */
    private array $maskRegexArray = [];

    private ?HighlightIntervals $highlightIntervals = null;

    private array $foundStems = [];

    public function __construct(string $line, int $formatId, StemmerInterface $stemmer, array $stemsFoundSomewhere, float $relevance)
    {
        $this->line                = $line;
        $this->formatId            = $formatId;
        $this->stemmer             = $stemmer;
        $this->stemsFoundSomewhere = $stemsFoundSomewhere;
        $this->relevance           = $relevance;
    }

    public static function createFromSnippetSourceWithoutFoundWords(SnippetSource $snippetSource): self
    {
        return new static(
            $snippetSource->getText(),
            $snippetSource->getFormatId(),
            new class implements StemmerInterface {
                public function stemWord(string $word, bool $normalize = true): string
                {
                    return $word;
                }
            },
            [],
            0
        );
    }

    public function getRelevance(): float
    {
        return $this->relevance;
    }

    /**
     * @return string[]
     * @deprecated Not used anymore. TODO delete if not needed
     */
    public function getFoundStems(): array
    {
        $this->parse();

        return $this->foundStems;
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

        $this->parse();

        $line = $this->getLineWithoutMaskedFragments();

        $replacedLine      = '';
        $processedPosition = 0;
        foreach ($this->highlightIntervals->toArray() as [$start, $end]) {
            $replacedLine  .= substr($line, $processedPosition, $start - $processedPosition);
            $lineToReplace = substr($line, $start, $end - $start);

            [$openFormatting, $closeFormatting] = StringHelper::getUnbalancedInternalFormatting($lineToReplace);

            // Open formatting goes to the end
            $outsidePostfix = implode('', array_map(static fn(string $char) => '\\' . $char, $openFormatting));
            $insidePostfix  = implode('', array_map(static fn(string $char) => '\\' . strtoupper($char), array_reverse($openFormatting)));

            // Close formatting goes to the start
            $outsidePrefix = implode('', array_map(static fn(string $char) => '\\' . $char, $closeFormatting));
            $insidePrefix  = implode('', array_map(static fn(string $char) => '\\' . strtolower($char), array_reverse($closeFormatting)));

            $replacedLine .= $outsidePrefix . sprintf(
                    $highlightTemplate, $insidePrefix . $lineToReplace . $insidePostfix
                ) . $outsidePostfix;

            $processedPosition = $end;
        }

        $replacedLine .= substr($line, $processedPosition);

        $result = $this->restoreMaskedFragments($replacedLine);

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

    protected function parse(): void
    {
        if ($this->highlightIntervals !== null) {
            // Already parsed
            return;
        }

        $this->highlightIntervals = new HighlightIntervals();

        $line = $this->getLineWithoutMaskedFragments();

        if (\count($this->stemsFoundSomewhere) === 0) {
            return;
        }

        if ($this->formatId === SnippetSource::FORMAT_INTERNAL) {
            $regex = '/(?x)
            [\\d\\p{L}^_]*(?:(?:\\\\[' . StringHelper::FORMATTING_SYMBOLS . '])+[\\d\\p{L}^_]*)* # matches as many word and formatting characters as possible
            (*SKIP) # do not cross this line on backtracking
            \\K # restart pattern matching to the end of the word.
            (?: # delimiter regex which includes:
                [^\\\\\\d\\p{L}^_\\-.,] # non-word character
                |[\\-.,]+(?![\\d\\p{L}\\-.,]) # [,-.] followed by a non-word character
                |\\\\(?:[' . StringHelper::FORMATTING_SYMBOLS . '](?![\\d\\p{L}\\-.,])|\\\\) # formatting sequence followed by a non-word character or escaped backslash
            )+/iu';
        } else {
            $regex = '/(?x)
            [\\d\\p{L}^_]* # matches as many word and formatting characters as possible
            (*SKIP) # do not cross this line on backtracking
            \\K # restart pattern matching to the end of the word.
            (?: # delimiter regex which includes:
                [^\\d\\p{L}^_\\-.,] # non-word character
                |[\\-.,]+(?![\\d\\p{L}\\-.,]) # [,-.] followed by a non-word character
            )+/iu';
        }
        $wordArray = preg_split($regex, $line, -1, \PREG_SPLIT_OFFSET_CAPTURE);

        $flippedStems = array_flip($this->stemsFoundSomewhere);
        foreach ($wordArray as [$rawWord, $offset]) {
            $word = $this->formatId === SnippetSource::FORMAT_INTERNAL ? StringHelper::clearInternalFormatting($rawWord) : $rawWord;
            $word = str_replace(self::STORE_MARKER, '', $word);

            if ($word === '') {
                // No need to call $intervals->skipInterval() since regex may work several times on a single delimiter
                continue;
            }

            $stem = null;
            if (isset($flippedStems[$word]) || isset($flippedStems[$stem = $this->stemmer->stemWord($word)])) {
                $this->highlightIntervals->addInterval($offset, $offset + \strlen($rawWord));
                $this->foundStems[] = $stem ?? $word;
            } else {
                // Word is not found. Check if it is like a hyphenated compound word, e.g. 'test-drive' or 'long-term'
                if (false !== strpbrk($stem, StringHelper::WORD_COMPONENT_DELIMITERS)) {
                    // Here is more simple regex since formatting sequences may be present.
                    // The downside is appearance of empty words, but they are filtered out later.
                    $subWordArray = preg_split('#[\-.,]+#u', $rawWord, -1, \PREG_SPLIT_OFFSET_CAPTURE);
                    foreach ($subWordArray as [$rawSubWord, $subOffset]) {
                        $subWord = $this->formatId === SnippetSource::FORMAT_INTERNAL ? StringHelper::clearInternalFormatting($rawSubWord) : $rawSubWord;
                        $subWord = str_replace(self::STORE_MARKER, '', $subWord);

                        if ($rawSubWord === '') {
                            continue;
                        }

                        $subStem = null;
                        if (isset($flippedStems[$subWord]) || isset($flippedStems[$subStem = $this->stemmer->stemWord($subWord)])) {
                            $this->highlightIntervals->addInterval($offset + $subOffset, $offset + $subOffset + \strlen($rawSubWord));
                            $this->foundStems[] = $subStem ?? $subWord;
                        } else {
                            $this->highlightIntervals->skipInterval();
                        }
                    }
                } else {
                    // Not a compound word
                    $this->highlightIntervals->skipInterval();
                }
            }
        }
    }

    protected function getLineWithoutMaskedFragments(): string
    {
        if ($this->lineWithoutMaskedFragments !== null) {
            return $this->lineWithoutMaskedFragments;
        }

        // Remove substrings that are not store markers
        $this->lineWithoutMaskedFragments = str_replace(self::STORE_MARKER, '', $this->line);

        $this->lineWithoutMaskedFragments = htmlspecialchars($this->lineWithoutMaskedFragments, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);

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
