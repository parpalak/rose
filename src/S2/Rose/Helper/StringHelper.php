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
        $text2 = preg_replace('#(\p{Lu}\p{L}*\.?)\s+(\p{Lu}\p{L}?\.)\s+(\p{Lu})#u',"\\1�\\2�\\3", $text);
        $text2 = preg_replace('#(\p{Lu}\p{L}?\.)(\p{Lu}\p{L}?\.)\s+(\p{Lu})#u',"\\1\\2�\\3", $text2);
        $text2 = preg_replace('#\s\K(Mr.|Dr.)\s(?=\p{Lu}\p{L}?)#u',"\\1�\\3", $text2);

        $substrings = preg_split('#[.?!]\K([ \n\t\r]+)(?=[^\p{Ll}])#Su', $text2);

        $substrings = str_replace("�", ' ', $substrings);

        return $substrings;
    }

    /**
     * @return string[]
     */
    public static function sentencesFromCode(string $text): array
    {
        $substrings = preg_split('#(\r?\n\r?){2,}#Su', $text);
        array_walk($substrings, 'trim');

        return $substrings;
    }
}
