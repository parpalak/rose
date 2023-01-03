<?php
/**
 * @copyright 2017-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Entity;

use Codeception\Test\Unit;
use S2\Rose\Entity\Snippet;
use S2\Rose\Entity\SnippetLine;

/**
 * Class SnippetTest
 *
 * @group snippet
 */
class SnippetTest extends Unit
{
    public function testSnippet1()
    {
        $snippetLine1 = new SnippetLine(
            'Testing string to highlight some test values.',
            ['test'],
            1
        );

        $snippetLine2 = new SnippetLine(
            'Test is case-sensitive.',
            ['Test', 'is'],
            2
        );

        $snippet = new Snippet('introduction', 2, '<i>%s</i>');
        $snippet
            ->attachSnippetLine(0, $snippetLine1)
            ->attachSnippetLine(1, $snippetLine2)
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
                'Тут есть тонкость - нужно проверить, как происходит экранировка в сущностях вроде &plus;.',
                ['сущностях'],
            ],
            [
                3,
                'Для этого нужно включить в текст само сочетание букв "plus".',
                ['plus'],
            ],
        ];

        $snippet = new Snippet('introduction', 2, '<i>%s</i>');

        foreach ($data as $row) {
            $snippet->attachSnippetLine($row[0], new SnippetLine($row[1], $row[2], count($row[2])));
        }

        $this->assertEquals(
            'Тут есть тонкость - нужно проверить, как происходит экранировка в <i>сущностях</i> вроде &plus;. Для этого нужно включить в текст само сочетание букв "<i>plus</i>".',
            $snippet->toString()
        );
    }

    public function testEmptySnippet()
    {
        $snippet = new Snippet('introduction', 0, '<i>%s</i>');
        $snippet->toString();

        $snippet = new Snippet('introduction', 0, '<i>%s</i>');
        $snippet->attachSnippetLine(1, new SnippetLine('line1', [], 0));
        $snippet->toString();
    }
}
