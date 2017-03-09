<?php
/**
 * @copyright 2016-2017 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Stemmer;

use Codeception\Test\Unit;
use S2\Rose\Stemmer\PorterStemmerRussian;
use S2\Rose\Stemmer\StemmerInterface;

/**
 * Class StemmerTest
 *
 * @group stem
 */
class StemmerTest extends Unit
{
    /**
     * @var StemmerInterface
     */
    protected $stemmer;

    protected function _before()
    {
        $this->stemmer = new PorterStemmerRussian();
    }

    protected function _after()
    {
    }

    public function testStem()
    {
        $this->assertEquals('ухмыля', $this->stemmer->stemWord('ухмыляться'));
        $this->assertEquals('рраф', $this->stemmer->stemWord('Ррафа'));
        $this->assertEquals('учител', $this->stemmer->stemWord('учитель'));
    }
}
