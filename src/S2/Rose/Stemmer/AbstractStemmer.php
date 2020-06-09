<?php
/**
 * @copyright 2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Stemmer;

abstract class AbstractStemmer implements IrregularWordsStemmerInterface
{
    protected $nextStemmer;

    public function __construct(StemmerInterface $nextStemmer = null)
    {
        $this->nextStemmer = $nextStemmer;
    }

    /**
     * {@inheritdoc}
     */
    public function irregularWordsFromStems(array $stems)
    {
        $flippedStems = array_flip($stems);
        $words = array_keys(array_filter($this->getIrregularWords(), static function ($irregularStem) use ($flippedStems) {
            return isset($flippedStems[$irregularStem]);
        }));

        if ($this->nextStemmer instanceof IrregularWordsStemmerInterface) {
            $words = array_merge($words, $this->nextStemmer->irregularWordsFromStems($stems));
        }

        return $words;
    }

    /**
     * @return array|string[]
     */
    abstract protected function getIrregularWords();
}
