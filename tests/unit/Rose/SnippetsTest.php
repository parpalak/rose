<?php /** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection PhpUnhandledExceptionInspection */

/**
 * @copyright 2017-2024 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test;

use Codeception\Test\Unit;
use S2\Rose\Entity\Indexable;
use S2\Rose\Entity\Query;
use S2\Rose\Entity\ResultItem;
use S2\Rose\Finder;
use S2\Rose\Indexer;
use S2\Rose\Stemmer\PorterStemmerRussian;
use S2\Rose\Stemmer\StemmerInterface;
use S2\Rose\Storage\Database\PdoStorage;
use S2\Rose\Storage\StorageReadInterface;
use S2\Rose\Storage\StorageWriteInterface;

/**
 * @group snippet
 * @group snippet-builder
 */
class SnippetsTest extends Unit
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

    public function _before()
    {
        global $s2_rose_test_db;

        $pdo = new \PDO($s2_rose_test_db['dsn'], $s2_rose_test_db['username'], $s2_rose_test_db['passwd']);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->readStorage  = new PdoStorage($pdo, 'test_');
        $this->writeStorage = new PdoStorage($pdo, 'test_');
        $this->writeStorage->erase();

        $this->stemmer = new PorterStemmerRussian();
        $this->indexer = new Indexer($this->writeStorage, $this->stemmer);
        $this->finder  = new Finder($this->readStorage, $this->stemmer);
        $this->finder->setHighlightTemplate('<span class="highlight">%s</span>');
        $this->finder->setHighlightMaskRegexArray(['#\$\$(?:[^$]++|\$(?!\$))*+\$\$#']);
    }

    /**
     * @dataProvider indexableProvider
     *
     * @param Indexable[] $indexables
     *
     * @throws \Exception
     */
    public function testSnippets(array $indexables)
    {
        foreach ($indexables as $indexable) {
            $this->indexer->index($indexable);
        }

        $resultSet = $this->finder->find(new Query('механическая природа'));

        $this->assertEquals(
            'Если пренебречь малыми величинами, то видно, что <span class="highlight">механическая природа</span> устойчиво требует большего внимания к анализу ошибок, которые даёт устойчивый маховик.',
            $resultSet->getItems()[0]->getSnippet()
        );

        // Check if highlighting works with different upper and lower cases.
        $resultSet = $this->finder->find(new Query('если пренебречь'));

        $this->assertEquals(
            '<span class="highlight">Если</span> основание движется с постоянным ускорением, проекция угловых скоростей вращает колебательный успокоитель качки... В самом общем случае маховик заставляет перейти к более сложной системе дифференциальных уравнений, <span class="highlight">если</span> добавить устойчивый гиротахометр... <span class="highlight">Если пренебречь</span> малыми величинами, то видно, что механическая природа устойчиво требует большего внимания к анализу ошибок, которые даёт устойчивый маховик.',
            $resultSet->getItems()[0]->getSnippet()
        );

        // Check line separators
        $resultSet = $this->finder->find(new Query('если'));
        $this->assertEquals(
            '<span class="highlight">Если</span> основание движется с постоянным ускорением, проекция угловых скоростей вращает колебательный успокоитель качки... В самом общем случае маховик заставляет перейти к более сложной системе дифференциальных уравнений, <span class="highlight">если</span> добавить устойчивый гиротахометр... Ошибка астатически даёт более простую систему дифференциальных уравнений, <span class="highlight">если</span> исключить небольшой угол тангажа.',
            $resultSet->getItems()[0]->getSnippet()
        );

        $this->assertEquals(
            '<span class="highlight">Если</span> основание движется с постоянным ускорением, проекция угловых скоростей вращает колебательный успокоитель качки... В самом общем случае маховик заставляет перейти к более сложной системе дифференциальных уравнений, <span class="highlight">если</span> добавить устойчивый гиротахометр... Ошибка <i>астатически</i> даёт более простую систему дифференциальных уравнений, <span class="highlight">если</span> исключить небольшой угол тангажа.',
            $resultSet->getItems()[0]->getFormattedSnippet()
        );

        $this->finder->setSnippetLineSeparator(' &middot; ');
        $resultSet = $this->finder->find(new Query('если'));
        $this->assertEquals(
            '<span class="highlight">Если</span> основание движется с постоянным ускорением, проекция угловых скоростей вращает колебательный успокоитель качки. &middot; В самом общем случае маховик заставляет перейти к более сложной системе дифференциальных уравнений, <span class="highlight">если</span> добавить устойчивый гиротахометр. &middot; Ошибка астатически даёт более простую систему дифференциальных уравнений, <span class="highlight">если</span> исключить небольшой угол тангажа.',
            $resultSet->getItems()[0]->getSnippet()
        );

        // Highlighting 'мне' as a word found by stem 'я'
        $resultSet = $this->finder->find(new Query('Мне не душно'));
        $this->assertEquals(
            '<span class="highlight">Мне не душно</span>',
            $resultSet->getItems()[0]->getHighlightedTitle($this->stemmer)
        );

        $this->assertEquals(
            '<span class="highlight">Я</span> просто <span class="highlight">не</span> ощущаю уровень углекислого газа в воздухе. <span class="highlight">Меня не</span> устраивает.',
            $resultSet->getItems()[0]->getSnippet()
        );

        // Highlighting 'ё'
        $resultSet = $this->finder->find(new Query('твердыми'));

        $this->assertSimilar(
            [
                'Артемий как абсолютно <span class="highlight">твёрдое</span> тело заставляет иначе взглянуть на то, что такое объект.',
                'Согласно теории Э.Тоффлера ("Шок будущего"), коллапс Советского Союза иллюстрирует <span class="highlight">твердый</span> экзистенциальный континентально-европейский тип политической культуры.',
            ],
            array_map(static function (ResultItem $item) {
                return $item->getSnippet();
            }, $resultSet->getItems())
        );

        $resultSet = $this->finder->find(new Query('твёрдая'));

        $this->assertSimilar(
            [
                'Артемий как абсолютно <span class="highlight">твёрдое</span> тело заставляет иначе взглянуть на то, что такое объект.',
                'Согласно теории Э.Тоффлера ("Шок будущего"), коллапс Советского Союза иллюстрирует <span class="highlight">твердый</span> экзистенциальный континентально-европейский тип политической культуры.',
            ],
            array_map(static function (ResultItem $item) {
                return $item->getSnippet();
            }, $resultSet->getItems())
        );

        $resultSet = $this->finder->find(new Query('артемий'));

        $this->assertSimilar(
            [
                'Политическое учение <span class="highlight">Артёма</span>, в первом приближении, формирует экзистенциальный социализм.',
                '<span class="highlight">Артемий</span> как абсолютно твёрдое тело заставляет иначе взглянуть на то, что такое объект.',
            ],
            array_map(static function (ResultItem $item) {
                return $item->getSnippet();
            }, $resultSet->getItems())
        );

        $this->assertSimilar(
            [
                'Почему неоднозначна борьба <span class="highlight">Артёма</span> против демократических и олигархических тенденций?',
                'Почему апериодичен маховик?',
            ],
            array_map(function (ResultItem $item) {
                return $item->getHighlightedTitle($this->stemmer);
            }, $resultSet->getItems())
        );

        $resultSet = $this->finder->find(new Query('1 защита xss gt'));
        $this->assertEquals(
            'Еще <span class="highlight">1</span> раз проверим, как <span class="highlight">gt</span> работает <span class="highlight">защита</span> против &lt;script&gt;alert();&lt;/script&gt; <span class="highlight">xss</span>-уязвимостей.',
            $resultSet->getItems()[0]->getSnippet()
        );

        $resultSet = $this->finder->find(new Query('подсистема'));
        $this->assertEquals(
            'Теория политических <span class="highlight">подсистем</span> нетривиальна, что такое <span class="highlight">подсистема</span>?',
            $resultSet->getItems()[0]->getSnippet(),
            'Stemmer trims incorrectly подсистем to подсист. Check that this incorrect behaviour is handled without bugs.'
        );

        $resultSet = $this->finder->find(new Query('астатически дает'));
        $this->assertEquals(
            'Ошибка <span class="highlight"><i>астатически</i> даёт</span> более простую систему дифференциальных уравнений, если исключить небольшой угол тангажа. Если пренебречь малыми величинами, то видно, что механическая природа устойчиво требует большего внимания к анализу ошибок, которые <span class="highlight">даёт</span> устойчивый маховик.',
            $resultSet->getItems()[0]->getFormattedSnippet()
        );

        $resultSet = $this->finder->find(new Query('Об одной из ошибок в веб-дизайне'));
        $this->assertEquals(
            '<span class="highlight">Об одной из ошибок в веб-дизайне</span>',
            $resultSet->getItems()[0]->getHighlightedTitle($this->stemmer)
        );
        $this->assertEquals(
            '<span class="highlight">Одна из</span> часто указываемых <span class="highlight">ошибок в веб-дизайне</span>:',
            $resultSet->getItems()[0]->getFormattedSnippet()
        );

        $resultSet = $this->finder->find(new Query('fastcgi_cache_lock_age'));
        $this->assertEquals(
            '<span class="highlight">fastcgi_cache_lock_age</span> 9s;',
            $resultSet->getItems()[0]->getFormattedSnippet()
        );

        $resultSet = $this->finder->find(new Query('nu'));
        $this->assertEquals(
            'Абзац с формулой с буквой <span class="highlight">nu</span>, которая не должна подсвечиваться в формуле $$E=h\nu$$. А это просто строка с формулой $$g{\mu\nu}$$.',
            $resultSet->getItems()[0]->getFormattedSnippet()
        );

        $resultSet = $this->finder->find(new Query('mu'));
        $this->assertEquals(
            'А это просто строка с формулой $$g{\mu\nu}$$.',
            $resultSet->getItems()[0]->getFormattedSnippet()
        );

        $resultSet = $this->finder->find(new Query('экстремум'));
        $this->assertEquals(
            '<span class="highlight">Экстр<i>е</i>мум</span> функции, в первом приближении, <sub><b>восстанавливает абстрактный</b></sub> разрыв функции.',
            $resultSet->getItems()[0]->getFormattedSnippet()
        );

        $resultSet = $this->finder->find(new Query('разрыв абстрактно'));
        $this->assertEquals(
            'Экстр<i>е</i>мум функции, в первом приближении, <sub><b>восстанавливает </b></sub><span class="highlight"><sub><b>абстрактный</b></sub> разрыв</span> функции. &middot; Отсюда естественно следует, что интеграл от функции, имеющий конечный <span class="highlight">разрыв</span> обуславливает тригонометрический интеграл по поверхности, явно демонстрируя всю чушь вышесказанного. В соответствии с законом больших чисел, интеграл Пуассона стремительно обуславливает положительный <span class="highlight">разрыв</span> функции.',
            $resultSet->getItems()[0]->getFormattedSnippet()
        );

        $resultSet = $this->finder->find(new Query('восстанавливать приближение'));
        $this->assertEquals(
            'Экстр<i>е</i>мум функции, в первом <span class="highlight">приближении, <sub><b>восстанавливает</b></sub></span><sub><b> абстрактный</b></sub> разрыв функции. &middot; Линейное программирование, в первом <span class="highlight">приближении</span>, необходимо и достаточно. &middot; Метод последовательных <span class="highlight">приближений</span>, следовательно, реально создает график функции.',
            $resultSet->getItems()[0]->getFormattedSnippet()
        );
    }

    public function indexableProvider()
    {
        $indexables = [
            new Indexable('id_1', 'Почему неоднозначна борьба Артёма против демократических и олигархических тенденций?', 'Политическое учение Артёма, в первом приближении, формирует экзистенциальный социализм. Типология средств массовой коммуникации сохраняет эмпирический политический процесс в современной России. Доиндустриальный тип политической культуры, несмотря на внешние воздействия, неизбежен. Общеизвестно, что политическое учение Н. Макиавелли взаимно. Либерализм, особенно в условиях политической нестабильности, определяет либерализм. Постиндустриализм неоднозначен.

Натуралистическая парадигма, короче говоря, ограничивает экзистенциальный референдум. Политический процесс в современной России определяет гуманизм. Иначе говоря, политическая культура практически представляет собой механизм власти. Теория политических подсистем нетривиальна, что такое подсистема?

Технология коммуникации обретает онтологический референдум, утверждает руководитель аппарата Правительства. Согласно теории Э.Тоффлера ("Шок будущего"), коллапс Советского Союза иллюстрирует твердый экзистенциальный континентально-европейский тип политической культуры. Марксизм вызывает современный референдум. В данном случае можно согласиться с Данилевским, считавшим, что информационно-технологическая революция сохраняет экзистенциальный референдум.'),
            new Indexable('id_2', 'Анормальный предел последовательности: предпосылки и развитие', 'Функция выпуклая кверху вырождена. Функция многих переменных положительна. Экстр<i>е</i>мум функции, в первом приближении, <sub><b>восстанавливает абстрактный</b></sub> разрыв функции. Несмотря на сложности, аффинное преобразование реально отражает интеграл от функции, обращающейся в бесконечность вдоль линии. Теорема порождает интеграл от функции, обращающейся в бесконечность вдоль линии, откуда следует доказываемое равенство.

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

<div class="index-skip">
<p>Не должно проиндексироваться.</p>
</div>

<p><img src="1.jpg" width="300" height="200">Внешнее кольцо позволяет пренебречь колебаниями корпуса, хотя развития этого в любом случае требует угол крена, поэтому энергия гироскопического маятника на неподвижной оси остаётся неизменной. Если основание движется с постоянным ускорением, проекция угловых скоростей вращает колебательный успокоитель качки. Артемий как абсолютно твёрдое тело заставляет иначе взглянуть на то, что такое объект. В самом общем случае маховик заставляет перейти к более сложной системе дифференциальных уравнений, если добавить устойчивый гиротахометр. Система координат, несмотря на внешние воздействия, трансформирует силовой трёхосный гироскопический стабилизатор.</p>

<p><img src="2.jpg" width="300" height="200">

<blockquote>А это цитата, ее тоже надо индексировать.</blockquote>

<pre><code>fastcgi_cache i_upmath;
fastcgi_cache_valid 200 10m;
fastcgi_cache_methods GET HEAD;
fastcgi_cache_lock on;
fastcgi_cache_lock_age 9s;
fastcgi_cache_lock_timeout 9s;</code></pre>

<img src="3.jpg" width="300" height="200">

<p>Ошибка <i>астатически</i> даёт более простую систему дифференциальных уравнений, если исключить небольшой угол тангажа. Если пренебречь малыми величинами, то видно, что механическая природа устойчиво требует большего внимания к анализу ошибок, которые даёт устойчивый маховик. Исходя из уравнения Эйлера, прибор вертикально позволяет пренебречь колебаниями корпуса, хотя этого в любом случае требует поплавковый ньютонометр.</p>

<p>Абзац с формулой с буквой nu, которая не должна подсвечиваться в формуле $$E=h\nu$$. А это просто строка с формулой $$g{\mu\nu}$$.</p>

<p>Уравнение возмущенного движения поступательно характеризует подвижный объект. Прецессия гироскопа косвенно интегрирует нестационарный вектор угловой скорости, изменяя направление движения. Угловая скорость, обобщая изложенное, неподвижно не входит своими составляющими, что очевидно, в силы нормальных реакций связей, так же как и кожух. Динамическое уравнение Эйлера, в силу третьего закона Ньютона, вращательно связывает ньютонометр, не забывая о том, что интенсивность диссипативных сил, характеризующаяся величиной коэффициента D, должна лежать в определённых пределах. Еще 1 раз проверим, как gt работает защита против &lt;script&gt;alert();&lt;/script&gt; xss-уязвимостей.</p>'),
            new Indexable('id_4', 'Мне не душно', 'Я просто не ощущаю уровень углекислого газа в воздухе. Меня не устраивает.'),
            new Indexable('id_5', 'Об одной из ошибок в веб-дизайне', 'Одна из часто указываемых ошибок в веб-дизайне:'),
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
