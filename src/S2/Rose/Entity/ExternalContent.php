<?php
/**
 * @copyright 2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

use S2\Rose\SnippetBuilder;

class ExternalContent
{
    private $externalIds = [];
    private $texts = [];

    /**
     * @param ExternalId $externalId
     * @param string     $text
     */
    public function attach(ExternalId $externalId, $text)
    {
        $this->externalIds[] = $externalId;
        $this->texts[]       = $text;
    }

    public function iterate(\Closure $callback)
    {
        foreach ($this->cleanContent() as $index => $text) {
            $callback($this->externalIds[$index], $text);
        }
    }

    /**
     * @return string[]
     */
    protected function cleanContent()
    {
        // Text cleanup
        $replaceFrom = [SnippetBuilder::LINE_SEPARATOR, '&nbsp;', '&mdash;', '&ndash;', '&laquo;', '&raquo;'];
        $replaceTo   = ['', ' ', '—', '–', '«', '»'];
        foreach ([
                     '<br>',
                     '<br />',
                     '</h1>',
                     '</h2>',
                     '</h3>',
                     '</h4>',
                     '</p>',
                     '</pre>',
                     '</blockquote>',
                     '</li>',
                 ] as $tag) {
            $replaceFrom[] = $tag;
            $replaceTo[]   = $tag . SnippetBuilder::LINE_SEPARATOR;
        }

        $contentArray = str_replace($replaceFrom, $replaceTo, $this->texts);
        foreach ($contentArray as &$string) {
            $string = strip_tags($string);
        }
        unset($string);

        // Preparing for breaking into lines
        $contentArray = preg_replace('#(?<=[\.?!;])[ \n\t]+#sS', SnippetBuilder::LINE_SEPARATOR, $contentArray);

        return $contentArray;
    }
}
