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

    /**
     * Special method that returns transformation rules for stems provided by the stemmer.
     *
     * Transformation rules are regular expression patterns used to convert stems into patterns for
     * searching words in the text that may match the stem. Each rule consists of a key, which is a
     * regular expression to be applied to every stem returned by the stemmer, and a value,
     * which is the replaced part of resulting regex applied to the text.
     *
     * For instance, in the English Porter stemmer, words like 'legacy' have the stem 'legaci'.
     * To find words in the text with the stem 'legaci', a pattern like '\wlegac[iy]' is required.
     * Therefore, the English Porter stemmer should return a rule like ['#i$#i' => '[iy]']
     * that replaces the last entry of 'i' into entry of either 'i' or 'y'.
     *
     * Possible false positive matches are not mistakes since found matches are checked
     * through the stemmer.
     *
     * @return mixed
     */
    public function getRegexTransformationRules(): array;
}
