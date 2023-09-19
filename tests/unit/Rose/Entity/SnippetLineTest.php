<?php
/**
 * @copyright 2017-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Entity;

use Codeception\Test\Unit;
use S2\Rose\Entity\Metadata\SnippetSource;
use S2\Rose\Entity\SnippetLine;
use S2\Rose\Exception\RuntimeException;

/**
 * Class SnippetLineTest
 *
 * @group snippet
 */
class SnippetLineTest extends Unit
{
    public function testCreateHighlighted1()
    {
        $snippetLine = new SnippetLine(
            'Testing string to highlight some test values, Test is case-sensitive.',
            SnippetSource::FORMAT_PLAIN_TEXT,
            ['test', 'is'],
            2
        );

        $this->assertEquals('Testing string to highlight some <i>test</i> values, Test <i>is</i> case-sensitive.', $snippetLine->getHighlighted('<i>%s</i>'));
    }

    public function testCreateHighlighted2()
    {
        $snippetLine = new SnippetLine(
            'Testing string to highlight some test values, Test is case-sensitive.',
            SnippetSource::FORMAT_PLAIN_TEXT,
            ['Test'],
            1
        );

        $this->assertEquals('Testing string to highlight some test values, <i>Test</i> is case-sensitive.', $snippetLine->getHighlighted('<i>%s</i>'));
    }

    public function testJoinHighlighted()
    {
        $snippetLine = new SnippetLine(
            'Testing string to highlight some test values, Test is case-sensitive.',
            SnippetSource::FORMAT_PLAIN_TEXT,
            ['to', 'highlight'],
            1
        );

        $this->assertEquals('Testing string <i>to highlight</i> some test values, Test is case-sensitive.', $snippetLine->getHighlighted('<i>%s</i>'));
    }

    public function testCreateHighlightedFail()
    {
        $snippetLine = new SnippetLine(
            'Testing string to highlight some test values, Test is case-sensitive.',
            SnippetSource::FORMAT_PLAIN_TEXT,
            ['test', 'is'],
            2
        );
        $this->expectException(RuntimeException::class);
        $snippetLine->getHighlighted('<i></i>');
    }
}
