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
    public function testSentences(string $text, array $sentences): void
    {
        foreach (StringHelper::sentencesFromText($text, false) as $i => $str) {
            $this->assertEquals($sentences[$i], $str);
        }
    }

    public function sentenceDataProvider(): array
    {
        // Лектор спросил: «В чем смысл названия курса?» Я попытался вспомнить, что он говорил на первой лекции, и воспроизвести его слова.
        return [
            ['One sentence.', ['One sentence.']],
            ['Second sentence.  And a third one 123.', ['Second sentence.', 'And a third one 123.']],
            ['Текст на русском. И еще предложение. 1, 2, 3 и т. д. Цифры, буквы, и т. п., могут встретиться.', [
                'Текст на русском.',
                'И еще предложение.',
                '1, 2, 3 и т. д.',
                'Цифры, буквы, и т. п., могут встретиться.',
            ]],
            [
                'Поезд отправился из пункта А в пункт Б. Затем вернулся назад.',
                [
                    'Поезд отправился из пункта А в пункт Б.',
                    'Затем вернулся назад.',
                ]],
            [
                'Это пример абзаца. Он содержит несколько предложений. Каждое предложение заканчивается точкой! Иногда используется вопросительный знак? И восклицательный знак! Иногда используются многоточия... Но это не всегда так.',
                [
                    'Это пример абзаца.',
                    'Он содержит несколько предложений.',
                    'Каждое предложение заканчивается точкой!',
                    'Иногда используется вопросительный знак?',
                    'И восклицательный знак!',
                    'Иногда используются многоточия...',
                    'Но это не всегда так.',
                ]
            ],
            [
                '- Прямая речь тоже разбивается на предложения? – Да, безусловно! — Отлично, то, что нужно. - Пожалуйста.',
                [
                    '- Прямая речь тоже разбивается на предложения?',
                    '– Да, безусловно!',
                    '— Отлично, то, что нужно.',
                    '- Пожалуйста.',
                ]
            ],
            [
                '"Прямая речь может быть в другом синтаксисе", - сказал я. Противник добавил: «Как это скучно!» И следом: «Как это так». Такие дела.',
                [
                    '"Прямая речь может быть в другом синтаксисе", - сказал я.',
                    'Противник добавил: «Как это скучно!»',
                    'И следом: «Как это так».',
                    'Такие дела.',
                ]
            ],
            [
                'На первом курсе А. П. Петров вел математику. А. П. Петров делал это хорошо. Все радовались А.П. Петрову. А.П. Петров пел математику.',
                [
                    'На первом курсе А. П. Петров вел математику.',
                    'А. П. Петров делал это хорошо.',
                    'Все радовались А.П. Петрову.',
                    'А.П. Петров пел математику.',
                ]
            ],
            [
                'Last week, former director of the F.B.I. James B. Comey was fired. Mr. Comey was not available for comment.',
                [
                    'Last week, former director of the F.B.I. James B. Comey was fired.',
                    'Mr. Comey was not available for comment.',
                ]
            ],
            [
                'На первом курсе А. П. Петров (зам. декана), Д. А. Александров (преподаватель физики) и несколько студентов нашего факультета (я в том числе) отправились в Тверь на проведение окружного этапа школьной олимпиады по физике.',
                [
                    'На первом курсе А. П. Петров (зам. декана), Д. А. Александров (преподаватель физики) и несколько студентов нашего факультета (я в том числе) отправились в Тверь на проведение окружного этапа школьной олимпиады по физике.',
                ]
            ],
        ];
    }

    public function testFixUnbalancedInternalFormatting(): void
    {
        $this->assertEquals('\\iThis is \\bformatted text\\I with \\Bspecial characters\\i.\\I', StringHelper::fixUnbalancedInternalFormatting('\\iThis is \\bformatted text\\I with \\Bspecial characters\\i.'));
        $this->assertEquals('', StringHelper::fixUnbalancedInternalFormatting(''));
        $this->assertEquals('456789i', StringHelper::fixUnbalancedInternalFormatting('456789i'));
        $this->assertEquals('\\i456789\\I', StringHelper::fixUnbalancedInternalFormatting('456789\\I'));
        $this->assertEquals('\\u456789\\U', StringHelper::fixUnbalancedInternalFormatting('\\u456789'));
        $this->assertEquals('\\i\\d\\u\\D\\\\I\\b\\B\\U', StringHelper::fixUnbalancedInternalFormatting('\\u\\D\\\\I\\b'));
    }
}
