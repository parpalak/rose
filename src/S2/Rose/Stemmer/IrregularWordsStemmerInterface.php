<?php
/**
 * @copyright 2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Stemmer;

interface IrregularWordsStemmerInterface
{
    /**
     * @param string[] $stems
     *
     * @return string[]
     */
    public function irregularWordsFromStems(array $stems);
}
