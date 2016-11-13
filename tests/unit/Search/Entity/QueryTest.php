<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Entity;

use Codeception\Test\Unit;
use S2\Rose\Entity\Query;

/**
 * Class FinderTest
 *
 * @group entity
 */
class QueryTest extends Unit
{
	public function testFilterInput()
	{
		$this->assertEquals([1, 2], (new Query('1|||2'))->valueToArray());
		$this->assertEquals([1, 2], (new Query('1\\\\\\2'))->valueToArray());
		$this->assertEquals(['a', 'b'], (new Query('a/b'))->valueToArray());
		$this->assertEquals(['a', 'b'], (new Query(' a   b   '))->valueToArray());
	}
}
