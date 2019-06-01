<?php
/**
 * @copyright 2017 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

use S2\Rose\Stemmer\StemmerInterface;

/**
 * Class FulltextQuery
 */
class FulltextQuery
{
    /**
     * @var string[]
     */
    protected $words = [];

    /**
     * @var array|string[]
     */
    protected $additionalStems = [];

    /**
     * FulltextQuery constructor.
     *
     * @param string[]         $words
     * @param StemmerInterface $stemmer
     */
    public function __construct(array $words, StemmerInterface $stemmer)
    {
        $this->words = array_values($words);
        $this->extractStems($stemmer);
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return count($this->words);
    }

    /**
     * @param StemmerInterface $stemmer
     */
    protected function extractStems(StemmerInterface $stemmer)
    {
        foreach ($this->words as $i => $word) {
            $stemWord = $stemmer->stemWord($word);
            if ($stemWord != $word) {
                $this->additionalStems[$i] = $stemWord;
            }
        }
    }

    /**
     * @return string[]
     */
    public function getWordsWithStems()
    {
        return array_merge($this->words, $this->additionalStems);
    }

    /**
     * @return WordPositionContainer
     */
    public function toWordPositionContainer()
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
