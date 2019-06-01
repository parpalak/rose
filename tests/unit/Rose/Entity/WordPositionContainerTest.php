<?php
/**
 * @copyright 2017-2018 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Entity;

use Codeception\Test\Unit;
use S2\Rose\Entity\WordPositionContainer;

/**
 * Class WordPositionContainerTest
 *
 * @group container
 */
class WordPositionContainerTest extends Unit
{
    public function testClosestDistance()
    {
        $container = new WordPositionContainer([
            'word1' => [23, 56, 74],
            'word2' => [2, 57],
        ]);

        $this->assertEquals(1, $container->getClosestDistanceBetween('word1', 'word2', 0));
        $this->assertEquals(-1, $container->getClosestDistanceBetween('word2', 'word1', 0));
        $this->assertEquals(23 - 2 - 20, $container->getClosestDistanceBetween('word2', 'word1', 20));
        $this->assertEquals(23 - 2 - 25, $container->getClosestDistanceBetween('word2', 'word1', 25));
    }

    public function testCompare()
    {
        $container = new WordPositionContainer();
        foreach (explode(' ', 'Циркуляция вектора напряженности электростатического поля вдоль замкнутого контура всегда равна нулю') as $k => $word) {
            $container->addWordAt($word, $k);
        }

        $this->assertEquals([['поля', 'нулю', 7]], $container->compareWith(new WordPositionContainer([
            'нулю' => [5],
            'нул'  => [5],
            'поля' => [6],
            'пол'  => [6],
        ])));

        $this->assertEquals([['поля', 'нулю', 5]], $container->compareWith(new WordPositionContainer([
            'нулю' => [1],
            'нул'  => [1],
            'поля' => [0],
            'пол'  => [0],
        ])));

        $this->assertEquals([
            ['вектора', 'поля', 2],
            ['вектора', 'контура', 4],
            ['поля', 'контура', 2],
        ], $container->compareWith(new WordPositionContainer([
            'вектора' => [1],
            'поля'    => [2],
            'контура' => [3],
        ])));
    }
}
