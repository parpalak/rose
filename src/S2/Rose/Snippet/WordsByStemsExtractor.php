<?php
/**
 * @copyright 2024 Roman Parpalak
 * @license MIT
 * @package S2
 */

declare(strict_types=1);

namespace S2\Rose\Snippet;

use S2\Rose\Helper\StringHelper;
use S2\Rose\Stemmer\IrregularWordsStemmerInterface;
use S2\Rose\Stemmer\StemmerInterface;

class WordsByStemsExtractor
{
    private StemmerInterface $stemmer;
    /**
     * @var string[]
     */
    private array $stems;
    private string $joinedStems;

    /**
     * @param string[] $stems
     */
    public function __construct(StemmerInterface $stemmer, array $stems)
    {
        $this->stemmer = $stemmer;
        $this->stems   = $stems;

        $stemsForRegex = $stems;
        if ($stemmer instanceof IrregularWordsStemmerInterface) {
            $stems = array_merge($stems, $stemmer->irregularWordsFromStems($stems));

            $regexRules          = $stemmer->getRegexTransformationRules();
            $regexRules['#\\.#'] = '\\.'; // escaping dot in the following preg_match_all() call
            $stemsForRegex       = array_map(static fn(string $stem): string => preg_replace(
                array_keys($regexRules),
                array_values($regexRules),
                $stem
            ), $stems);
        }

        $this->joinedStems = implode('|', $stemsForRegex);
    }

    public function extract(string $text): array
    {
        // Check the text for the query words
        // NOTE: Make sure the modifier S works correct on cyrillic
        // TODO: After implementing formatting this regex became a set of crutches.
        // One has to break the snippets into words, clear formatting, convert words to stems
        // and detect what stems have been found. Then highlight the original text based on words source offset.
        preg_match_all(
            '#(?<=[^\\p{L}-]|^|\\\\[' . StringHelper::FORMATTING_SYMBOLS . '])(' . $this->joinedStems . ')[\\p{L}-]*#Ssui',
            $text,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        $foundWords = $foundStems = [];
        foreach ($matches[0] as $i => $wordInfo) {
            foreach ($this->getWords($wordInfo[0]) as $word) {
                $stemEqualsWord = ($word === $matches[1][$i][0]);
                $stemmedWord    = $this->stemmer->stemWord($word);

                // Ignore entry if the word stem differs from needed ones
                if (!$stemEqualsWord && !\in_array($stemmedWord, $this->stems, true)) {
                    continue;
                }

                $foundWords[$word]        = 1;
                $foundStems[$stemmedWord] = 1;
            }
        }

        return [$foundWords, $foundStems];
    }

    /**
     * If there is no hyphen in the word, use it as the found word.
     * If the word contains a hyphen, besides checking the entire word,
     * check each fragment for a match with the searched stem.
     *
     * @param string $text
     * @return string[]
     */
    private function getWords(string $text): array
    {
        if (strpos($text, '-') === false) {
            return [$text];
        }

        return array_merge(explode('-', $text), [$text]);
    }
}
