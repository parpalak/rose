<?php
/**
 * @copyright 2016-2019 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Stemmer;

use Codeception\Test\Unit;
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
    private $chainedStemmer1;

    /**
     * @var StemmerInterface
     */
    private $chainedStemmer2;

    public function _before()
    {
        $this->russianStemmer  = new PorterStemmerRussian();
        $this->englishStemmer  = new PorterStemmerEnglish();
        $this->chainedStemmer1 = new PorterStemmerRussian(new PorterStemmerEnglish());
        $this->chainedStemmer2 = new PorterStemmerEnglish(new PorterStemmerRussian());
    }

    public function _after()
    {
    }

    public function testStem()
    {
        $this->assertEquals('ухмыляться', $this->englishStemmer->stemWord('ухмыляться'));
        $this->assertEquals('ухмыля', $this->russianStemmer->stemWord('ухмыляться'));
        $this->assertEquals('ухмыля', $this->chainedStemmer1->stemWord('ухмыляться'));
        $this->assertEquals('ухмыля', $this->chainedStemmer2->stemWord('ухмыляться'));

        $this->assertEquals('рраф', $this->russianStemmer->stemWord('Ррафа'));

        $this->assertEquals('учитель', $this->englishStemmer->stemWord('Учитель'));
        $this->assertEquals('учител', $this->russianStemmer->stemWord('учитель'));
        $this->assertEquals('учител', $this->chainedStemmer1->stemWord('учитель'));
        $this->assertEquals('учител', $this->chainedStemmer2->stemWord('учитель'));

        $this->assertEquals('gun', $this->englishStemmer->stemWord('guns'));
        $this->assertEquals('guns', $this->russianStemmer->stemWord('guns'));

        $this->assertEquals('papa', $this->chainedStemmer1->stemWord('papa\'s'));
        $this->assertEquals('papa', $this->chainedStemmer2->stemWord('papa\'s'));
    }
}
