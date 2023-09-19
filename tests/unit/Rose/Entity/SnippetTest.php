<?php
/**
 * @copyright 2017-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Entity;

use Codeception\Test\Unit;
use S2\Rose\Entity\Metadata\SnippetSource;
use S2\Rose\Entity\Snippet;
use S2\Rose\Entity\SnippetLine;

/**
 * @group snippet
 */
class SnippetTest extends Unit
{
    public function testSnippet1()
    {
        $snippetLine1 = new SnippetLine(
            'Testing string to highlight some test values.',
            SnippetSource::FORMAT_PLAIN_TEXT,
            ['test'],
            1
        );

        $snippetLine2 = new SnippetLine(
            'Test is case-sensitive.',
            SnippetSource::FORMAT_PLAIN_TEXT,
            ['Test', 'is'],
            2
        );

        $snippet = new Snippet('introduction', 2, '<i>%s</i>');
        $snippet
            ->attachSnippetLine(0, 6, $snippetLine1)
            ->attachSnippetLine(7, 9, $snippetLine2)
        ;

        $this->assertEquals(
            'Testing string to highlight some <i>test</i> values. <i>Test is</i> case-sensitive.',
            $snippet->toString()
        );
    }

    public function testSnippet2()
    {
        $data = [
            [
                2,
                13,
                'Тут есть тонкость - нужно проверить, как происходит экранировка в сущностях вроде &plus;.',
                ['сущностях'],
            ],
            [
                14,
                23,
                'Для этого нужно включить в текст само сочетание букв "plus".',
                ['plus'],
            ],
        ];

        $snippet = new Snippet('introduction', 2, '<i>%s</i>');

        foreach ($data as $row) {
            $snippet->attachSnippetLine($row[0], $row[1], new SnippetLine($row[2], SnippetSource::FORMAT_PLAIN_TEXT, $row[3], count($row[3])));
        }

        $this->assertEquals(
            'Тут есть тонкость - нужно проверить, как происходит экранировка в <i>сущностях</i> вроде &amp;plus;. Для этого нужно включить в текст само сочетание букв "<i>plus</i>".',
            $snippet->toString()
        );
    }

    public function testSnippetsUnique()
    {
        $snippet = new Snippet('introduction', 1, '<i>%s</i>');
        $snippet
            ->attachSnippetLine(0, 3, new SnippetLine('Try to test 1.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 1))
            ->attachSnippetLine(4, 7, new SnippetLine('Try to test 1.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 1))
            ->attachSnippetLine(8, 11, new SnippetLine('Try to test 1.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 1))
            ->attachSnippetLine(12, 15, new SnippetLine('Try to test 1.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 1))
            ->attachSnippetLine(16, 19, new SnippetLine('Try to test 2.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 1))
            ->attachSnippetLine(20, 23, new SnippetLine('Try to test 2.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 1))
            ->attachSnippetLine(24, 27, new SnippetLine('Try to test 2.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 1))
        ;

        $this->assertEquals(
            'Try to <i>test</i> 1... Try to <i>test</i> 2.',
            $snippet->toString(0.6)
        );

        $snippet = new Snippet('introduction', 1, '<i>%s</i>');
        $snippet
            ->attachSnippetLine(0 * 4, 0 * 4 + 3, new SnippetLine('Try to test 1.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 1))
            ->attachSnippetLine(1 * 4, 1 * 4 + 3, new SnippetLine('Try to test 1.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 1))
            ->attachSnippetLine(2 * 4, 2 * 4 + 3, new SnippetLine('Try to test 1.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 1))
            ->attachSnippetLine(3 * 4, 3 * 4 + 3, new SnippetLine('Try to test 1.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 1))
            ->attachSnippetLine(4 * 4, 4 * 4 + 3, new SnippetLine('Try to test 2.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 1))
            ->attachSnippetLine(5 * 4, 5 * 4 + 3, new SnippetLine('Try to test 2.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 1))
            ->attachSnippetLine(6 * 4, 6 * 4 + 3, new SnippetLine('Try to test 2.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 1))
            ->attachSnippetLine(7 * 4, 7 * 4 + 3, new SnippetLine('Try to test 3.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 1))
            ->attachSnippetLine(8 * 4, 8 * 4 + 3, new SnippetLine('Try to test 3.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 1))
            ->attachSnippetLine(9 * 4, 9 * 4 + 3, new SnippetLine('Try to test 3.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 1))
            ->attachSnippetLine(10 * 4, 10 * 4 + 3, new SnippetLine('Try to test 4.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 1))
            ->attachSnippetLine(11 * 4, 11 * 4 + 3, new SnippetLine('Try to test 4.', SnippetSource::FORMAT_PLAIN_TEXT, ['test'], 2))
        ;

        $this->assertEquals(
            'Try to <i>test</i> 1... Try to <i>test</i> 2... Try to <i>test</i> 4.',
            $snippet->toString(0.6)
        );
    }

    public function testEmptySnippet()
    {
        $snippet = new Snippet('introduction', 0, '<i>%s</i>');
        $snippet->toString();

        $snippet = new Snippet('introduction', 0, '<i>%s</i>');
        $snippet->attachSnippetLine(1, 1, new SnippetLine('line1', SnippetSource::FORMAT_PLAIN_TEXT, [], 0));
        $snippet->toString();
    }
}
