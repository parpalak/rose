<?php declare(strict_types=1);
/**
 * @copyright 2017-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

use S2\Rose\Stemmer\StemmerInterface;

class FulltextQuery
{
    /**
     * @var string[]
     */
    protected array $words = [];

    /**
     * @var array|string[]
     */
    protected array $additionalStems = [];

    /**
     * @param string[] $words
     */
    public function __construct(array $words, StemmerInterface $stemmer)
    {
        $this->words = array_values($words);
        $this->extractStems($stemmer);
    }

    protected function extractStems(StemmerInterface $stemmer): void
    {
        foreach ($this->words as $i => $word) {
            $stemWord = $stemmer->stemWord($word);
            if ($stemWord !== $word) {
                $this->additionalStems[$i] = $stemWord;
            }
        }
    }

    /**
     * @return string[]
     */
    public function getWordsWithStems(): array
    {
        return array_merge($this->words, $this->additionalStems);
    }

    public function toWordPositionContainer(): WordPositionContainer
    {
        $container = new WordPositionContainer();

        foreach ($this->words as $position => $word) {
            $container->addWordAt($word, $position);
        }

        foreach ($this->additionalStems as $position => $stem) {
            $container->addWordAt($stem, $position);
        }

        return $container;
    }
}
