<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Extractor;

use S2\Rose\Extractor\HtmlDom\DomExtractor;
use S2\Rose\Extractor\HtmlRegex\RegexExtractor;

class DefaultExtractorFactory
{
    public static function create(): ChainExtractor
    {
        $extractor = new ChainExtractor();
        if (DomExtractor::available()) {
            $extractor->attachExtractor(new DomExtractor());
        }
        $extractor->attachExtractor(new RegexExtractor());

        return $extractor;
    }
}
