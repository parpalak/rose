<?php declare(strict_types=1);
/**
 * @copyright 2020-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Helper;

class StringHelper
{
    public const BOLD        = 'b';
    public const ITALIC      = 'i';
    public const SUPERSCRIPT = 'u';
    public const SUBSCRIPT   = 'd';

    public const FORMATTING_SYMBOLS = self::BOLD . self::ITALIC . self::SUPERSCRIPT . self::SUBSCRIPT;

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
    public static function sentencesFromText(string $text, bool $hasFormatting): array
    {
        $text2 = preg_replace('#(\p{Lu}\p{L}*\.?)\s+(\p{Lu}\p{L}?\.)\s+(\p{Lu})#u', "\\1�\\2�\\3", $text);
        $text2 = preg_replace('#(\p{Lu}\p{L}?\.)(\p{Lu}\p{L}?\.)\s+(\p{Lu})#u', "\\1\\2�\\3", $text2);
        $text2 = preg_replace('#\s\K(Mr.|Dr.)\s(?=\p{Lu}\p{L}?)#u', "\\1�\\3", $text2);

        $substrings = preg_split('#(?:\.|[?!][»"]?)\K([ \n\t\r]+)(?=(?:[\p{Pd}-]\s)?[^\p{Ll}])#Su', $text2);

        $substrings = str_replace("�", ' ', $substrings);

        if ($hasFormatting) {
            // We keep the formatting scope within a single sentence.
            //
            // For example, consider the input: 'Sentence <i>1. Sentence 2. Sentence</i> 3.'
            // After processing, it becomes ['Sentence <i>1.</i>', 'Sentence 2.', '<i>Sentence</i> 3.'].
            //
            // This approach is reasonable because individual sentences are typically joined into snippets,
            // and preserving formatting across multiple sentences may not be meaningful.
            array_walk($substrings, static function (string &$text) {
                $text = self::fixUnbalancedInternalFormatting($text);
            });
        }

        return $substrings;
    }

    /**
     * @return string[]
     */
    public static function sentencesFromCode(string $text): array
    {
        $substrings = preg_split('#(\r?\n\r?){1,}#Su', $text);
        array_walk($substrings, 'trim');

        return $substrings;
    }

    public static function convertInternalFormattingToHtml(string $text): string
    {
        return strtr($text, [
            '\\\\'                               => '\\',
            '\\' . self::BOLD                    => '<b>',
            '\\' . strtoupper(self::BOLD)        => '</b>',
            '\\' . self::ITALIC                  => '<i>',
            '\\' . strtoupper(self::ITALIC)      => '</i>',
            '\\' . self::SUBSCRIPT               => '<sub>',
            '\\' . strtoupper(self::SUBSCRIPT)   => '</sub>',
            '\\' . self::SUPERSCRIPT             => '<sup>',
            '\\' . strtoupper(self::SUPERSCRIPT) => '</sup>',
        ]);
    }

    public static function clearInternalFormatting(string $text): string
    {
        return strtr($text, [
            '\\\\'                               => '\\',
            '\\' . self::BOLD                    => '',
            '\\' . strtoupper(self::BOLD)        => '',
            '\\' . self::ITALIC                  => '',
            '\\' . strtoupper(self::ITALIC)      => '',
            '\\' . self::SUBSCRIPT               => '',
            '\\' . strtoupper(self::SUBSCRIPT)   => '',
            '\\' . self::SUPERSCRIPT             => '',
            '\\' . strtoupper(self::SUPERSCRIPT) => '',
        ]);
    }

    public static function fixUnbalancedInternalFormatting(string $text): string
    {
        preg_match_all('#\\\\([' . self::FORMATTING_SYMBOLS . '])#i', $text, $matches);

        $tagsNum = [];
        foreach ($matches[1] as $match) {
            $lowerMatch           = strtolower($match);
            $tagsNum[$lowerMatch] = ($tagsNum[$lowerMatch] ?? 0) + ($match === $lowerMatch ? 1 : -1);
        }

        $result = $text;

        foreach ($tagsNum as $possibleTag => $num) {
            if ($num < 0) {
                $result = str_repeat('\\' . $possibleTag, -$num) . $result;
            }
        }
        $tagsNum = array_reverse($tagsNum);
        foreach ($tagsNum as $possibleTag => $num) {
            if ($num > 0) {
                $result .= str_repeat('\\' . strtoupper($possibleTag), $num);
            }
        }

        return $result;
    }
}
