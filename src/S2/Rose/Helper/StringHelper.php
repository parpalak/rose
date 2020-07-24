<?php
/**
 * @copyright 2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Helper;

class StringHelper
{
    /**
     * @param array $words
     *
     * @return string[]
     */
    public static function removeLongWords(array $words)
    {
        return array_values(array_filter($words, static function ($word) {
            $len = mb_strlen($word);

            return $len > 0 && $len <= 100;
        }));
    }
}
