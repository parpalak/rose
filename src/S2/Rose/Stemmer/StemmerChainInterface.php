<?php
/**
 * @copyright 2016-2019 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Stemmer;

interface StemmerChainInterface extends StemmerInterface
{
    /**
     * @param string $word
     *
     * @return bool
     */
    public function supports($word);
}
