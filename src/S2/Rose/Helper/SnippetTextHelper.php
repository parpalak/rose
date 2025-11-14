<?php
/**
 * @copyright 2025 Roman Parpalak
 * @license   https://opensource.org/license/mit MIT
 */

declare(strict_types=1);

namespace S2\Rose\Helper;

use S2\Rose\Entity\Metadata\SnippetSource;

class SnippetTextHelper
{
    public const STORE_MARKER = "\r";

    /**
     * @param string[] $maskRegexArray
     */
    public static function sanitize(string $text, array $maskRegexArray, array &$maskedFragments): string
    {
        $text = str_replace(self::STORE_MARKER, '', $text);
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);

        $maskRegexArray = array_merge($maskRegexArray, ['#&(?:\#[1-9]\d{1,3}|[A-Za-z][0-9A-Za-z]+);#']);
        foreach ($maskRegexArray as $maskRegex) {
            $text = preg_replace_callback(
                $maskRegex,
                function (array $matches) use (&$maskedFragments) {
                    $maskedFragments[] = $matches[0];

                    return self::STORE_MARKER;
                },
                $text
            );
        }

        return $text;
    }

    public static function restore(string $text, array $maskedFragments): string
    {
        $i = 0;
        while (true) {
            $pos = strpos($text, self::STORE_MARKER);
            if ($pos === false || !isset($maskedFragments[$i])) {
                break;
            }

            $text = substr_replace($text, $maskedFragments[$i], $pos, \strlen(self::STORE_MARKER));
            $i++;
        }

        return $text;
    }

    public static function convertFormatting(string $text, int $formatId, bool $includeFormatting): string
    {
        if ($formatId !== SnippetSource::FORMAT_INTERNAL) {
            return $text;
        }

        return $includeFormatting
            ? StringHelper::convertInternalFormattingToHtml($text)
            : StringHelper::clearInternalFormatting($text);
    }

    /**
     * @param string[] $maskRegexArray
     */
    public static function prepareForOutput(string $text, int $formatId, bool $includeFormatting, array $maskRegexArray = []): string
    {
        $maskedFragments = [];
        $sanitized       = self::sanitize($text, $maskRegexArray, $maskedFragments);

        return self::convertFormatting(self::restore($sanitized, $maskedFragments), $formatId, $includeFormatting);
    }
}
