<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Helper;

use Codeception\Test\Unit;
use S2\Rose\Helper\StringHelper;

/**
 * @group string
 */
class StringHelperTest extends Unit
{
    /**
     * @dataProvider sentenceDataProvider
     */
    public function testSentences(string $text, array $sentences, array $offsets, array $lengths): void
    {
        foreach (StringHelper::sentencesFromText($text) as $i => $str) {
            $this->assertEquals($sentences[$i], $str);
        }
    }

    public function sentenceDataProvider(): array
    {
        return [
            ['One sentence.', ['One sentence.'], [0], [13]],
            ['Second sentence.  And a third one 123.', ['Second sentence.', 'And a third one 123.'], [0, 18], [16, 20]],
            ['Текст на русском. И еще предложение.', ['Текст на русском.', 'И еще предложение.'], [0, 18], [17, 18]],
        ];
    }
}
