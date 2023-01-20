<?php declare(strict_types=1);
/**
 * @copyright 2016-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Stemmer;

interface StemmerInterface
{
    public function stemWord(string $word, bool $normalize = true): string;
}
