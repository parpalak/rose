<?php
/**
 * @copyright 2016-2019 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Stemmer;

use Codeception\Test\Unit;
use S2\Rose\Stemmer\ChainedStemmer;
use S2\Rose\Stemmer\PorterStemmerEnglish;
use S2\Rose\Stemmer\PorterStemmerRussian;
use S2\Rose\Stemmer\StemmerInterface;

/**
 * @group stem
 */
class StemmerTest extends Unit
{
    /**
     * @var StemmerInterface
     */
    private $russianStemmer;

    /**
     * @var StemmerInterface
     */
    private $englishStemmer;

    /**
     * @var StemmerInterface
     */
    private $chainedStemmer;

    public function _before()
    {
        $this->russianStemmer = new PorterStemmerRussian();
        $this->englishStemmer = new PorterStemmerEnglish();
        $this->chainedStemmer = (new ChainedStemmer())->attach($this->englishStemmer)->attach($this->russianStemmer);
    }

    public function _after()
    {
    }

    public function testStem()
    {
        $this->assertEquals('ухмыля', $this->russianStemmer->stemWord('ухмыляться'));
        $this->assertEquals('ухмыля', $this->chainedStemmer->stemWord('ухмыляться'));
        $this->assertEquals('рраф', $this->russianStemmer->stemWord('Ррафа'));
        $this->assertEquals('учител', $this->russianStemmer->stemWord('учитель'));
        $this->assertEquals('gun', $this->englishStemmer->stemWord('guns'));
        $this->assertEquals('papa', $this->chainedStemmer->stemWord('papa\'s'));
    }
}
