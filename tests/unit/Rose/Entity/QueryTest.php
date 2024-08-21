<?php
/**
 * @copyright 2016-2024 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Entity;

use Codeception\Test\Unit;
use S2\Rose\Entity\Query;

/**
 * @group entity
 * @group query
 */
class QueryTest extends Unit
{
    public function testFilterInput(): void
    {
        // Tests for splitting strings by special delimiters
        $this->assertEquals([1, 2], (new Query('1|||2'))->valueToArray());
        $this->assertEquals([1, 2], (new Query('1\\\\\\2'))->valueToArray());
        $this->assertEquals(['a', 'b'], (new Query('a/b'))->valueToArray());
        $this->assertEquals(['a', 'b'], (new Query(' a   b   '))->valueToArray());
        $this->assertEquals(['..'], (new Query('..'))->valueToArray());
        $this->assertEquals(['...'], (new Query('...'))->valueToArray());
        $this->assertEquals(['a..b'], (new Query('a..b'))->valueToArray());

        // Tests for replacing numbers
        $this->assertEquals(['1.2'], (new Query('1,2'))->valueToArray());
        // $this->assertEquals(['-1.2'], (new Query('-1,2'))->valueToArray());
        $this->assertEquals(['1.2'], (new Query('1.2'))->valueToArray());

        // Tests for replacing typographic quotes
        $this->assertEquals(['"', 'text'], (new Query('«text»'))->valueToArray());
        $this->assertEquals(['"', 'text'], (new Query('“text”'))->valueToArray());

        // Tests for replacing dashes
        $this->assertEquals(['a--b'], (new Query('a--b'))->valueToArray());
        $this->assertEquals(['a—b'], (new Query('a---b'))->valueToArray()); // --- to mdash
        $this->assertEquals(['a—b'], (new Query('a–b'))->valueToArray()); // ndash to mdash
        $this->assertEquals(['a-b'], (new Query('a−b'))->valueToArray()); // Minus to hyphen

        // Test for replacing line breaks and extra spaces
        $this->assertEquals(['a', 'b'], (new Query("a\n\nb"))->valueToArray());
        $this->assertEquals(['a', 'b'], (new Query("a \t   b"))->valueToArray());

        // Tests for separating special characters
        $this->assertEquals(['a!b'], (new Query('a!b'))->valueToArray());
        $this->assertEquals(['!', 'ab'], (new Query('!ab'))->valueToArray());
        $this->assertEquals(['!', 'a!b'], (new Query('!a!b'))->valueToArray());
        $this->assertEquals(['(', 'word', ')'], (new Query('(word)'))->valueToArray());
        $this->assertEquals(['mysql', '--all-databases'], (new Query('mysql --all-databases'))->valueToArray());

        // Test for replacing "ё" with "е"
        $this->assertEquals(['ё', 'полет', 'field'], (new Query('ё полёт field'))->valueToArray());

        // Tests for handling commas
        $this->assertEquals(['a', ',', 'b'], (new Query('a,b'))->valueToArray());
        $this->assertEquals(['a', ',,', 'b'], (new Query('a,,b'))->valueToArray());
        $this->assertEquals(['a', ',,,', 'b'], (new Query('a,,,b'))->valueToArray());

        // Tests for removing long words
        $this->assertEquals(['a', 'c'], (new Query('a ' . str_repeat('b', 101) . ' c'))->valueToArray());

        // Tests for compatibility of multiple rules
        $this->assertEquals(['a—b', '"', 'text'], (new Query('a–b «text»'))->valueToArray());
        $this->assertEquals(['a', ',', 'b'], (new Query(" a, \n   b "))->valueToArray());
    }
}
