<?php /** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection PhpUnhandledExceptionInspection */

/**
 * @copyright 2017-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test;

use Codeception\Test\Unit;
use S2\Rose\Entity\ExternalContent;
use S2\Rose\Entity\Indexable;
use S2\Rose\Entity\Query;
use S2\Rose\Entity\ResultItem;
use S2\Rose\Finder;
use S2\Rose\Indexer;
use S2\Rose\SnippetBuilder;
use S2\Rose\Stemmer\PorterStemmerRussian;
use S2\Rose\Stemmer\StemmerInterface;
use S2\Rose\Storage\Database\PdoStorage;
use S2\Rose\Storage\StorageReadInterface;
use S2\Rose\Storage\StorageWriteInterface;

/**
 * @group snippet
 * @group snippet-builder
 */
class SnippetBuilderTest extends Unit
{
    /**
     * @var StorageReadInterface
     */
    protected $readStorage;

    /**
     * @var StorageWriteInterface
     */
    protected $writeStorage;

    /**
     * @var StemmerInterface
     */
    protected $stemmer;

    /**
     * @var Indexer
     */
    protected $indexer;

    /**
     * @var Finder
     */
    protected $finder;

    /**
     * @var SnippetBuilder
     */
    protected $snippetBuilder;

    public function _before()
    {
        global $s2_rose_test_db;

        $pdo = new \PDO($s2_rose_test_db['dsn'], $s2_rose_test_db['username'], $s2_rose_test_db['passwd']);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->readStorage  = new PdoStorage($pdo, 'test_');
        $this->writeStorage = new PdoStorage($pdo, 'test_');
        $this->writeStorage->erase();

        $this->stemmer        = new PorterStemmerRussian();
        $this->indexer        = new Indexer($this->writeStorage, $this->stemmer);
        $this->finder         = new Finder($this->readStorage, $this->stemmer);
        $this->snippetBuilder = new SnippetBuilder($this->stemmer);
    }

    /**
     * @dataProvider indexableProvider
     *
     * @param Indexable[] $indexables
     * @throws \Exception
     */
    public function testSnippets(array $indexables)
    {
        foreach ($indexables as $indexable) {
            $this->indexer->index($indexable);
        }

        $snippetCallbackProvider = static function (array $ids) use ($indexables) {
            $result = new ExternalContent();
            foreach ($indexables as $indexable) {
                foreach ($ids as $id) {
                    if ($indexable->getExternalId()->equals($id)) {
                        $result->attach($id, $indexable->getContent());
                    }
                }
            }

            return $result;
        };

        $resultSet = $this->finder->find(new Query('механическая природа'));
        $this->snippetBuilder->attachSnippets($resultSet, $snippetCallbackProvider);

        $this->assertEquals(
            'Если пренебречь малыми величинами, то видно, что <i>механическая природа</i> устойчиво требует большего внимания к анализу ошибок, которые даёт устойчивый маховик.',
            $resultSet->getItems()[0]->getSnippet()
        );

        // Check if highlighting works with different upper and lower cases.
        $resultSet = $this->finder->find(new Query('если пренебречь'));
        $this->snippetBuilder->attachSnippets($resultSet, $snippetCallbackProvider);

        $this->assertEquals(
            PHP_MAJOR_VERSION >= 7
                ? '<i>Если</i> основание движется с постоянным ускорением, проекция угловых скоростей вращает колебательный успокоитель качки... В самом общем случае маховик заставляет перейти к более сложной системе дифференциальных уравнений, <i>если</i> добавить устойчивый гиротахометр... <i>Если пренебречь</i> малыми величинами, то видно, что механическая природа устойчиво требует большего внимания к анализу ошибок, которые даёт устойчивый маховик.'
                : 'В самом общем случае маховик заставляет перейти к более сложной системе дифференциальных уравнений, <i>если</i> добавить устойчивый гиротахометр... Ошибка астатически даёт более простую систему дифференциальных уравнений, <i>если</i> исключить небольшой угол тангажа. <i>Если пренебречь</i> малыми величинами, то видно, что механическая природа устойчиво требует большего внимания к анализу ошибок, которые даёт устойчивый маховик.',
            $resultSet->getItems()[0]->getSnippet()
        );

        // Check line separators
        $resultSet = $this->finder->find(new Query('если'));
        $this->snippetBuilder->attachSnippets($resultSet, $snippetCallbackProvider);
        $this->assertEquals(
            PHP_MAJOR_VERSION >= 7
                ? '<i>Если</i> основание движется с постоянным ускорением, проекция угловых скоростей вращает колебательный успокоитель качки... В самом общем случае маховик заставляет перейти к более сложной системе дифференциальных уравнений, <i>если</i> добавить устойчивый гиротахометр... Ошибка астатически даёт более простую систему дифференциальных уравнений, <i>если</i> исключить небольшой угол тангажа.'
                : 'В самом общем случае маховик заставляет перейти к более сложной системе дифференциальных уравнений, <i>если</i> добавить устойчивый гиротахометр... Ошибка астатически даёт более простую систему дифференциальных уравнений, <i>если</i> исключить небольшой угол тангажа. <i>Если</i> пренебречь малыми величинами, то видно, что механическая природа устойчиво требует большего внимания к анализу ошибок, которые даёт устойчивый маховик.',
            $resultSet->getItems()[0]->getSnippet()
        );

        $resultSet = $this->finder->find(new Query('если'));
        $this->snippetBuilder->setSnippetLineSeparator(' &middot; ');
        $this->snippetBuilder->attachSnippets($resultSet, $snippetCallbackProvider);
        $this->assertEquals(
            PHP_MAJOR_VERSION >= 7
                ? '<i>Если</i> основание движется с постоянным ускорением, проекция угловых скоростей вращает колебательный успокоитель качки. &middot; В самом общем случае маховик заставляет перейти к более сложной системе дифференциальных уравнений, <i>если</i> добавить устойчивый гиротахометр. &middot; Ошибка астатически даёт более простую систему дифференциальных уравнений, <i>если</i> исключить небольшой угол тангажа.'
                : 'В самом общем случае маховик заставляет перейти к более сложной системе дифференциальных уравнений, <i>если</i> добавить устойчивый гиротахометр. &middot; Ошибка астатически даёт более простую систему дифференциальных уравнений, <i>если</i> исключить небольшой угол тангажа. <i>Если</i> пренебречь малыми величинами, то видно, что механическая природа устойчиво требует большего внимания к анализу ошибок, которые даёт устойчивый маховик.',
            $resultSet->getItems()[0]->getSnippet()
        );

        // Highlighting 'мне' as a word found by stem 'я'
        $resultSet = $this->finder->find(new Query('Мне не душно'));
        $this->snippetBuilder->attachSnippets($resultSet, $snippetCallbackProvider);
        $this->assertEquals(
            '<i>Мне не душно</i>',
            $resultSet->getItems()[0]->getHighlightedTitle($this->stemmer)
        );

        $this->assertEquals(
            '<i>Я</i> просто <i>не</i> ощущаю уровень углекислого газа в воздухе. <i>Меня не</i> устраивает.',
            $resultSet->getItems()[0]->getSnippet()
        );

        // Highlighting 'ё'
        $resultSet = $this->finder->find(new Query('твердыми'));
        $this->snippetBuilder->attachSnippets($resultSet, $snippetCallbackProvider);

        $this->assertSimilar(
            [
                'Артемий как абсолютно <i>твёрдое</i> тело заставляет иначе взглянуть на то, что такое объект.',
                'Согласно теории Э.Тоффлера ("Шок будущего"), коллапс Советского Союза иллюстрирует <i>твердый</i> экзистенциальный континентально-европейский тип политической культуры.'
            ],
            array_map(static function (ResultItem $item) {
                return $item->getSnippet();
            }, $resultSet->getItems())
        );

        $resultSet = $this->finder->find(new Query('твёрдая'));
        $this->snippetBuilder->attachSnippets($resultSet, $snippetCallbackProvider);

        $this->assertSimilar(
            [
                'Артемий как абсолютно <i>твёрдое</i> тело заставляет иначе взглянуть на то, что такое объект.',
                'Согласно теории Э.Тоффлера ("Шок будущего"), коллапс Советского Союза иллюстрирует <i>твердый</i> экзистенциальный континентально-европейский тип политической культуры.',
            ],
            array_map(static function (ResultItem $item) {
                return $item->getSnippet();
            }, $resultSet->getItems())
        );

        $resultSet = $this->finder->find(new Query('артемий'));
        $this->snippetBuilder->attachSnippets($resultSet, $snippetCallbackProvider);

        $this->assertSimilar(
            [
                'Политическое учение <i>Артёма</i>, в первом приближении, формирует экзистенциальный социализм.',
                '<i>Артемий</i> как абсолютно твёрдое тело заставляет иначе взглянуть на то, что такое объект.',
            ],
            array_map(static function (ResultItem $item) {
                return $item->getSnippet();
            }, $resultSet->getItems())
        );

        $this->assertSimilar(
            [
                'Почему неоднозначна борьба <i>Артёма</i> против демократических и олигархических тенденций?',
                'Почему апериодичен маховик?',
            ],
            array_map(function (ResultItem $item) {
                return $item->getHighlightedTitle($this->stemmer);
            }, $resultSet->getItems())
        );

        $resultSet = $this->finder->find(new Query('1 защита xss gt'));
        $this->snippetBuilder->attachSnippets($resultSet, $snippetCallbackProvider);
        $this->assertEquals(
            'Еще <i>1</i> раз проверим, как <i>gt</i> работает <i>защита</i> против &lt;script&gt;alert();&lt;/script&gt; <i>xss</i>-уязвимостей.',
            $resultSet->getItems()[0]->getSnippet()
        );
    }

    public function indexableProvider()
    {
        $indexables = [
            new Indexable('id_1', 'Почему неоднозначна борьба Артёма против демократических и олигархических тенденций?', 'Политическое учение Артёма, в первом приближении, формирует экзистенциальный социализм. Типология средств массовой коммуникации сохраняет эмпирический политический процесс в современной России. Доиндустриальный тип политической культуры, несмотря на внешние воздействия, неизбежен. Общеизвестно, что политическое учение Н. Макиавелли взаимно. Либерализм, особенно в условиях политической нестабильности, определяет либерализм. Постиндустриализм неоднозначен.

Натуралистическая парадигма, короче говоря, ограничивает экзистенциальный референдум. Политический процесс в современной России определяет гуманизм. Иначе говоря, политическая культура практически представляет собой механизм власти.

Технология коммуникации обретает онтологический референдум, утверждает руководитель аппарата Правительства. Согласно теории Э.Тоффлера ("Шок будущего"), коллапс Советского Союза иллюстрирует твердый экзистенциальный континентально-европейский тип политической культуры. Марксизм вызывает современный референдум. В данном случае можно согласиться с Данилевским, считавшим, что информационно-технологическая революция сохраняет экзистенциальный референдум.'),
            new Indexable('id_2', 'Анормальный предел последовательности: предпосылки и развитие', 'Функция выпуклая кверху вырождена. Функция многих переменных положительна. Экстремум функции, в первом приближении, восстанавливает абстрактный разрыв функции. Несмотря на сложности, аффинное преобразование реально отражает интеграл от функции, обращающейся в бесконечность вдоль линии. Теорема порождает интеграл от функции, обращающейся в бесконечность вдоль линии, откуда следует доказываемое равенство.

Линейное программирование, в первом приближении, необходимо и достаточно. Отсюда естественно следует, что интеграл от функции, имеющий конечный разрыв обуславливает тригонометрический интеграл по поверхности, явно демонстрируя всю чушь вышесказанного. В соответствии с законом больших чисел, интеграл Пуассона стремительно обуславливает положительный разрыв функции.

Артём доказал, как следует из вышесказанного, последовательно. Тем не менее, достаточное условие сходимости проецирует скачок функции. Метод последовательных приближений, следовательно, реально создает график функции. Метод последовательных приближений определяет интеграл по бесконечной области. Длина вектора, как следует из вышесказанного, неоднозначна. Геодезическая линия нейтрализует интеграл Фурье, как и предполагалось.'),
            new Indexable('id_3', 'Почему апериодичен маховик?', '<style>
div {
    background: 50% url(/pictures/img_1106.jpg) no-repeat #ccc;
    background-size: cover;
    padding-top: 56.25%;
}
@media screen and (min-aspect-ratio: 32 / 19) {
    div {
        padding-top: 50%;
    }
}
#header-crumbs.image-crumbs {
    background: hsla(156, 100%, 14%, 0.44);
    position: relative;
    z-index: 1;
    margin-bottom: -35px;
}
</style>

<p>Внешнее кольцо позволяет пренебречь колебаниями корпуса, хотя развития этого в любом случае требует угол крена, поэтому энергия гироскопического маятника на неподвижной оси остаётся неизменной. Если основание движется с постоянным ускорением, проекция угловых скоростей вращает колебательный успокоитель качки. Артемий как абсолютно твёрдое тело заставляет иначе взглянуть на то, что такое объект. В самом общем случае маховик заставляет перейти к более сложной системе дифференциальных уравнений, если добавить устойчивый гиротахометр. Система координат, несмотря на внешние воздействия, трансформирует силовой трёхосный гироскопический стабилизатор.</p>

<p>Ошибка астатически даёт более простую систему дифференциальных уравнений, если исключить небольшой угол тангажа. Если пренебречь малыми величинами, то видно, что механическая природа устойчиво требует большего внимания к анализу ошибок, которые даёт устойчивый маховик. Исходя из уравнения Эйлера, прибор вертикально позволяет пренебречь колебаниями корпуса, хотя этого в любом случае требует поплавковый ньютонометр.</p>

<p>Уравнение возмущенного движения поступательно характеризует подвижный объект. Прецессия гироскопа косвенно интегрирует нестационарный вектор угловой скорости, изменяя направление движения. Угловая скорость, обобщая изложенное, неподвижно не входит своими составляющими, что очевидно, в силы нормальных реакций связей, так же как и кожух. Динамическое уравнение Эйлера, в силу третьего закона Ньютона, вращательно связывает ньютонометр, не забывая о том, что интенсивность диссипативных сил, характеризующаяся величиной коэффициента D, должна лежать в определённых пределах. Еще 1 раз проверим, как gt работает защита против &lt;script&gt;alert();&lt;/script&gt; xss-уязвимостей.</p>'),
            new Indexable('id_4', 'Мне не душно', 'Я просто не ощущаю уровень углекислого газа в воздухе. Меня не устраивает.'),
        ];

        return [
            'db' => [$indexables],
        ];
    }

    private function assertSimilar(array $array1, array $array2)
    {
        $this->assertEquals([], array_diff($array1, $array2));
        $this->assertEquals([], array_diff($array2, $array1));
    }
}
