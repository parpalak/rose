<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Extractor\HtmlRegex;

use S2\Rose\Entity\ContentWithMetadata;
use S2\Rose\Entity\Metadata\ImgCollection;
use S2\Rose\Entity\Metadata\SentenceMap;
use S2\Rose\Entity\Metadata\SnippetSource;
use S2\Rose\Extractor\ExtractionErrors;
use S2\Rose\Extractor\ExtractionResult;
use S2\Rose\Extractor\ExtractorInterface;

class RegexExtractor implements ExtractorInterface
{
    private const PARAGRAPH_SEPARATOR = "\n";

    public function extract(string $text): ExtractionResult
    {
        $replaceFrom = [self::PARAGRAPH_SEPARATOR, SentenceMap::LINE_SEPARATOR, '<BR />', '<br />', '<BR>', '<br>',];
        $replaceTo   = [' ', '', SentenceMap::LINE_SEPARATOR, SentenceMap::LINE_SEPARATOR, SentenceMap::LINE_SEPARATOR, SentenceMap::LINE_SEPARATOR];

        foreach ([
                     '<hr>',
                     '<hr />',
                     '<HR>',
                     '<HR />',
                     '<img ',
                     '<IMG ',
                 ] as $tag) {
            $replaceFrom[] = $tag;
            $replaceTo[]   = ' ' . $tag;
        }

        foreach ([
                     '<h1>', '<h2>', '<h3>', '<h4>', '<h5>', '<h6>',
                     '<p>', '<pre>', '<blockquote>', '<li>',
                     '<H1>', '<H2>', '<H3>', '<H4>', '<H5>', '<H6>',
                     '<P>', '<PRE>', '<BLOCKQUOTE>', '<LI>',
                 ] as $tag) {
            $replaceFrom[] = $tag;
            $replaceTo[]   = self::PARAGRAPH_SEPARATOR . $tag;
        }
        foreach ([
                     '</h1>', '</h2>', '</h3>', '</h4>', '</h5>', '</h6>',
                     '</p>', '</pre>', '</blockquote>', '</li>', '</td>',
                     '</H1>', '</H2>', '</H3>', '</H4>', '</H5>', '</H6>',
                     '</P>', '</PRE>', '</BLOCKQUOTE>', '</LI>', '</TD>',
                 ] as $tag) {
            $replaceFrom[] = $tag;
            $replaceTo[]   = $tag . self::PARAGRAPH_SEPARATOR;
        }

        $text = str_replace($replaceFrom, $replaceTo, $text);

        $text = preg_replace('#<(script|style)[^>]*?>.*?</\\1>#si', '', $text);
        $text = preg_replace('#<([a-z]+) [^>]*?index-skip[^>]*?>.*?</\\1>#si', '', $text);

        $paragraphs = explode(self::PARAGRAPH_SEPARATOR, $text);
        $texts      = array_map(static fn(string $string) => trim(strip_tags($string)), $paragraphs); // TODO allow some formatting
        $texts      = array_filter($texts);

        $text = implode(' ', $texts);

        $text = html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);

        return new ExtractionResult(
            new ContentWithMetadata((new SentenceMap(SnippetSource::FORMAT_PLAIN_TEXT))->add(0, '', $text), new ImgCollection()),
            new ExtractionErrors()
        );
    }
}
