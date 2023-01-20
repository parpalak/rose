<?php declare(strict_types=1);
/**
 * @copyright 2020-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Helper;

class StringHelper
{
    public static function removeLongWords(array &$words): void
    {
        $removed = false;
        foreach ($words as $k => $word) {
            $len = mb_strlen($word);

            if ($len > 100 || $len === 0) {
                unset($words[$k]);
                $removed = true;
            }
        }
        if ($removed) {
            $words = array_values($words);
        }
    }

    /**
     * @return string[]
     */
    public static function sentencesFromText(string $text): array
    {
        // TODO improve algorithm
        $substrings = preg_split('#[.?!]\K([ \n\t]+)#S', $text);

        return $substrings;
    }
}
