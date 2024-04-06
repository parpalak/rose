<?php declare(strict_types=1);
/**
 * @copyright 2023-2024 Roman Parpalak
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
    public function testSentences(string $text, array $sentences, bool $hasFormatting = false): void
    {
        foreach (StringHelper::sentencesFromText($text, $hasFormatting) as $i => $str) {
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
            ['Sentence \i1. Sentence 2. Sentence\I 3.', ['Sentence \i1.\I', '\iSentence 2.\I', '\iSentence\I 3.'], true],
            ['Sentence \i1. Sentence 2. Sentence\B 3.', ['Sentence \i1.\I', '\iSentence 2.\I', '\b\iSentence\B 3.\I'], true],
            ['\i\uSentence \b1\B. Sentence 2. Sentence 3.\U\I', ['\i\uSentence \b1\B.\U\I', '\i\uSentence 2.\U\I', '\i\uSentence 3.\U\I'], true],
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

    /**
     * @dataProvider unbalancedInternalFormattingDataProvider
     */
    public function testFixUnbalancedInternalFormatting(string $text, string $expected, array $expectedTags): void
    {
        $tags = [];
        $this->assertEquals($expected, StringHelper::fixUnbalancedInternalFormatting($text, $tags));
        $this->assertEquals($expectedTags, $tags);
    }

    public function unbalancedInternalFormattingDataProvider(): array
    {
        return [
            [
                '\\iThis is \\bformatted text\\I with \\Bspecial characters\\i.',
                '\\iThis is \\bformatted text\\I with \\Bspecial characters\\i.\\I',
                ['i' => 1, 'b' => 0],
            ],
            [
                'Normal text with escaped formatting symbols like \\\\draw or \\\\inline or \\\\\\\\uuu.',
                'Normal text with escaped formatting symbols like \\\\draw or \\\\inline or \\\\\\\\uuu.',
                [],
            ],
            ['', '', []],
            ['456789i', '456789i', []],
            [
                '456789\\I',
                '\\i456789\\I',
                ['i' => -1],
            ],
            [
                '456789\\\\I',
                '456789\\\\I',
                [],
            ],
            [
                '456789\\\\\\I',
                '\\i456789\\\\\\I',
                ['i' => -1],
            ],
            [
                '456789\\\\\\\\I',
                '456789\\\\\\\\I',
                [],
            ],
            [
                '456789\\\\\\\\\\I',
                '\\i456789\\\\\\\\\\I',
                ['i' => -1],
            ],
            [
                '\\u456789',
                '\\u456789\\U',
                ['u' => 1],
            ],
            [
                '\\u\\D\\\\I\\b',
                '\\d\\u\\D\\\\I\\b\\B\\U',
                ['d' => -1, 'u' => 1, 'b' => 1],
            ],
        ];
    }
}
