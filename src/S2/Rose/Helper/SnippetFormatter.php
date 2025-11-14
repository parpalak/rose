<?php
/**
 * @copyright 2025 Roman Parpalak
 * @license   https://opensource.org/license/mit MIT
 */

declare(strict_types=1);

namespace S2\Rose\Helper;

use S2\Rose\Entity\Metadata\SnippetSource;
use S2\Rose\Entity\SnippetLine;

class SnippetFormatter
{
    public static function toOutput(string $text, int $formatId, bool $includeFormatting): string
    {
        $snippetSource = new SnippetSource($text, $formatId, 0, 0);
        $snippetLine   = SnippetLine::createFromSnippetSourceWithoutFoundWords($snippetSource);

        return $snippetLine->getHighlighted('%s', $includeFormatting);
    }
}
