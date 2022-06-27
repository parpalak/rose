<?php
/**
 * @copyright 2022 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Entity;

use Codeception\Test\Unit;
use S2\Rose\Entity\FulltextResult;

/**
 * @group entity
 */
class FulltextResultTest extends Unit
{
    public function testFrequencyReduction()
    {
        $this->assertEquals(0.9889808283708308, FulltextResult::frequencyReduction(50, 2));
        $this->assertEquals(0.17705374665950163, FulltextResult::frequencyReduction(50, 25));
        $this->assertEquals(1, FulltextResult::frequencyReduction(3, 2));
    }
}
