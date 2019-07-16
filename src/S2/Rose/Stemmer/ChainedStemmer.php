<?php
/**
 * @copyright 2019 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Stemmer;

class ChainedStemmer implements StemmerInterface
{
    /**
     * @var StemmerChainInterface[]
     */
    private $stemmers = [];

    /**
     * @param StemmerChainInterface $stemmer
     *
     * @return self
     */
    public function attach(StemmerChainInterface $stemmer)
    {
        $this->stemmers[] = $stemmer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function stemWord($word)
    {
        foreach ($this->stemmers as $stemmer) {
            if ($stemmer->supports($word)) {
                return $stemmer->stemWord($word);
            }
        }

        return $word;
    }
}
