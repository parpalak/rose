<?php declare(strict_types=1);
/**
 * @copyright 2020-2023 Roman Parpalak
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
    public function irregularWordsFromStems(array $stems): array;
}
