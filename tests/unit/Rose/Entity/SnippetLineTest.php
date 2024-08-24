<?php
/**
 * @copyright 2017-2024 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Entity;

use Codeception\Test\Unit;
use S2\Rose\Entity\Metadata\SnippetSource;
use S2\Rose\Entity\SnippetLine;
use S2\Rose\Exception\RuntimeException;
use S2\Rose\Stemmer\PorterStemmerEnglish;

/**
 * @group snippet
 * @group snippet-line
 */
class SnippetLineTest extends Unit
{
    public function testCreateHighlighted1()
    {
        $snippetLine = new SnippetLine(
            'Testing string to highlight some test values, Test is case-sensitive.',
            SnippetSource::FORMAT_PLAIN_TEXT,
            new PorterStemmerEnglish(),
            ['test', 'is'],
            2
        );

        $this->assertEquals(
            '<i>Testing</i> string to highlight some <i>test</i> values, <i>Test is</i> case-sensitive.',
            $snippetLine->getHighlighted('<i>%s</i>', false)
        );
    }

    public function testCreateHighlighted2()
    {
        $snippetLine = new SnippetLine(
            'Testing string to highlight some test values, Test is case-sensitive.',
            SnippetSource::FORMAT_PLAIN_TEXT,
            new PorterStemmerEnglish(),
            ['Test'], // unknown stem, stems are normalized to lower case, however there is a match due to direct comparison
            1
        );

        $this->assertEquals(
            'Testing string to highlight some test values, <i>Test</i> is case-sensitive.',
            $snippetLine->getHighlighted('<i>%s</i>', false)
        );
    }

    public function testJoinHighlighted()
    {
        $snippetLine = new SnippetLine(
            'Testing string to highlight some test values, Test is case-sensitive.',
            SnippetSource::FORMAT_PLAIN_TEXT,
            new PorterStemmerEnglish(),
            ['to', 'highlight'],
            1
        );

        $this->assertEquals(
            'Testing string <i>to highlight</i> some test values, Test is case-sensitive.',
            $snippetLine->getHighlighted('<i>%s</i>', false)
        );
    }

    public function testCreateHighlightedFail()
    {
        $snippetLine = new SnippetLine(
            'Testing string to highlight some test values, Test is case-sensitive.',
            SnippetSource::FORMAT_PLAIN_TEXT,
            new PorterStemmerEnglish(),
            ['test', 'is'],
            2
        );
        $this->expectException(RuntimeException::class);
        $snippetLine->getHighlighted('<i></i>', false);
    }
}
