<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Extractor;

use Codeception\Test\Unit;
use S2\Rose\Extractor\ExtractorInterface;
use S2\Rose\Extractor\HtmlDom\DomExtractor;
use S2\Rose\Extractor\HtmlRegex\RegexExtractor;
use S2\Rose\Helper\StringHelper;

/**
 * @group extract
 */
class ExtractorTest extends Unit
{
    private DomExtractor $domExtractor;
    private RegexExtractor $regexExtractor;

    /**
     * {@inheritdoc}
     */
    public function _before()
    {
        $this->domExtractor   = new DomExtractor();
        $this->regexExtractor = new RegexExtractor();
    }

    /**
     * @dataProvider htmlTextProvider
     */
    public function testRegexExtractor(string $htmlText, string $resultText): void
    {
        $extractionResult = $this->regexExtractor->extract($htmlText);
        $sentenceMap      = $extractionResult->getContentWithMetadata()->getSentenceMap();

        self::assertEquals(StringHelper::clearInternalFormatting($resultText), $sentenceMap->toSentenceCollection()->getText());
    }

    /**
     * @dataProvider htmlTextProvider
     */
    public function testDomExtractor(string $htmlText, string $resultText, ?array $words = null, $images = null): void
    {
        $extractionResult = $this->domExtractor->extract($htmlText);

        $sentenceMap = $extractionResult->getContentWithMetadata()->getSentenceMap();
        self::assertEquals($resultText, $sentenceMap->toSentenceCollection()->getText());
        if ($words !== null) {
            self::assertEquals($words, $sentenceMap->toSentenceCollection()->getWordsArray());
        }
        if ($images !== null) {
            self::assertEquals($images, $extractionResult->getContentWithMetadata()->getImageCollection()->toJson());
        }
    }

    public function testDomExtractionError(): void
    {
        $extractor = new DomExtractor();

        $extractionResult = $extractor->extract('<p>html text</p>');
        self::assertFalse($extractionResult->getErrors()->hasErrors());

        $extractionResult = $extractor->extract('Plain text');
        self::assertTrue($extractionResult->getErrors()->hasErrors());
        self::assertEquals(['1:? Found anonymous text block "Plain text". Consider using <p> tag as a text container. (code=anon_text)'], $extractionResult->getErrors()->getFormattedLines());

        $extractionResult = $extractor->extract('<b>unbalanced html</i>');
        self::assertTrue($extractionResult->getErrors()->hasErrors());
        self::assertEquals([
            '1:152 Unexpected end tag : i (code=76)',
            '1:159 Opening and ending tag mismatch: body and b (code=76)',
        ], $extractionResult->getErrors()->getFormattedLines());
    }

    /**
     * @dataProvider htmlSentenceProvider
     */
    public function testSentenceBreaking(ExtractorInterface $extractor, string $htmlText, array $sentences): void
    {
        $extractionResult = $extractor->extract($htmlText);
        $sentenceMap      = $extractionResult->getContentWithMetadata()->getSentenceMap();

        self::assertEquals($sentences, $sentenceMap->toSentenceCollection()->toArray());
    }

    public function htmlTextProvider(): array
    {
        return [
            ['Some <nobr>self-test</nobr>.', 'Some self-test.'],
            ['Some <noindex>self-test</noindex>.', 'Some self-test.'],
            ['Some text.<BR>Another text.<hr />Any other text.', 'Some text. Another text. Any other text.'],
            ['<P>One sentence.</P><p class="not-important">Another<br>sentence.</p>', 'One sentence. Another sentence.'],
            ['<P>One sentence.</P><!--excluded--><p>Another<br>sentence.</p>', 'One sentence. Another sentence.'],
            ['<P>One sentence.</P><noindex><p>Another<br>sentence.</p></noindex>', 'One sentence. Another sentence.'],
            ['<P>One sentence.</P><p>Another<img src="1.png" alt="" />sentence.</p>', 'One sentence. Another sentence.'],
            ['<p>One sentence.</p>List:<ul><li>First</li>  <li> Second<p>and a half  </p></li></ul>', 'One sentence. List: First Second and a half'],
            ['<P><i>This</i> sentence is a little bit <em>longer. And</em> this is not.</p>', '\\iThis\\I sentence is a little bit \\ilonger.\\I \\iAnd\\I this is not.'],
            ['<p>This <table><tr><td>is broken</td><td>HTML.</td></tr></table>I <b>want <i>to</b> test a</i> real-word <img><unknown-tag>example</p>', 'This is broken HTML. I \bwant \ito\I\B test a real-word example', ['this', 'is', 'broken', 'html', 'i', 'want', 'to', 'test', 'a', 'real-word', 'example']],
            [
                '<P><i>This</i> sentence&nbsp;contains entities like &#43;, &plus;, &planck;, &amp;, &lt;, &quot;, &#8212;, &laquo;, &#x2603;, &#x1D306;, &#xA9;, &copy;. &amp;plus; is not an entity.</p>',
                '\\iThis\\I sentence contains entities like +, +, ℏ, &, <, ", —, «, ☃, 𝌆, ©, ©. &plus; is not an entity.',
                ['this', 'sentence', 'contains', 'entities', 'like', 'ℏ', 'plus', 'is', 'not', 'an', 'entity'],
            ],
            [
                '<style>
div {
    background: 50% url(/pictures/img_1106.jpg) no-repeat #ccc;
    background-size: cover;
    padding-top: 56.25%;
}
/*<p>Not to be indexed</p>*/
@media screen and (min-aspect-ratio: 32 / 19) {
    div {
        padding-top: 50%;
    }
}
#header-crumbs.image-crumbs {
    z-index: 1;
}
</style>

<div class="index-skip">
<p>Не должно проиндексироваться.</p>
</div>

<noindex>

<p>Должно проиндексироваться.</p>

</noindex>

<p><img src="1.jpg" width="300" height="200">Внешнее кольцо позволяет пренебречь.</p>

<p><img src="https://localhost/2.jpg&amp;test=1" width="300" height="200" alt="valid escaped src and alt &amp; &rarr; &amp;rarr;">

<blockquote>
    А это цитата, ее тоже надо индексировать.
    <p>В цитате могут быть абзацы.</p>
</blockquote>

<img src="https://localhost/3.jpg&test=1" width="300" height="200" alt="invalid escaped src and alt &">

<p>Ошибка <i>астатически</i> даёт более простую систему.</p>

<p>Еще 1 раз проверим, как gt работает защита против &lt;script&gt;alert();&lt;/script&gt; xss-уязвимостей.</p>',
                'Должно проиндексироваться. Внешнее кольцо позволяет пренебречь. А это цитата, ее тоже надо индексировать. В цитате могут быть абзацы. Ошибка \\iастатически\\I даёт более простую систему. Еще 1 раз проверим, как gt работает защита против <script>alert();</script> xss-уязвимостей.',
                null,
                '[{"src":"1.jpg","width":"300","height":"200","alt":""},{"src":"https:\/\/localhost\/2.jpg&test=1","width":"300","height":"200","alt":"valid escaped src and alt & → &rarr;"},{"src":"https:\/\/localhost\/3.jpg&test=1","width":"300","height":"200","alt":"invalid escaped src and alt &"}]'
            ],
        ];
    }

    public function htmlSentenceProvider(): array
    {
        $source = '<p><span class="index-skip">00:11</span> Почему моим словам стоит доверять: 13 лет опыта<br />
<span class="index-skip">00:42</span> Собирался рассказать о понятии формата давно<br />
<span class="index-skip">01:11</span> Для затравки: чем плохи выпадайки в вебе, пример личного кабинета интернет-банка<br />
<span class="index-skip">03:28</span> Понятие формата<br />
<span class="index-skip">04:56</span> Пример 1: формат веба и формат окон настройки старых операционных систем (сравнение из <a href="https://habr.com/ru/post/143386/">старой статьи на хабре</a>)<br />
<span class="index-skip">08:03</span> <a href="https://habr.com/ru/post/143386/#comment_4805232">Комментарий к статье</a>, обращающийся к понятию формата<br />
<span class="index-skip">10:42</span> OS/2 умер<br />
<span class="index-skip">12:09</span> Окно настройки - почему такое? Ограничение 1: физический размер экранов<br />
<span class="index-skip">14:24</span> Ограничение 2: размер видеопамяти<br />
<span class="index-skip">15:26</span> Ограничение 3: частота обновления<br />
<span class="index-skip">18:53</span> Ограничение 4: работа без драйверов<br />
<span class="index-skip">19:54</span> 640*480 - естественное ограничение в конце 90-х<br />
<span class="index-skip">20:58</span> Особенности формата веба<br />
<span class="index-skip">22:47</span> Сравнивать надо функциональность<br />
<span class="index-skip">25:28</span> Бессмысленность претензий к вебу<br />
<span class="index-skip">26:52</span> Эволюция интерфейса настройки Windows<br />
<span class="index-skip">32:28</span> Пример 2: Одностраничные приложения<br />
<span class="index-skip">33:54</span> Админка моего движка как пример одностраничного приложения<br />
<span class="index-skip">37:25</span> Как бы сейчас спроектировал интерфейс админки<br />
<span class="index-skip">41:42</span> Обсуждаем извлеченные уроки и дизайн выпадайки из личного кабинета интернет-банка<br />
<span class="index-skip">46:17</span> Итог</p>';

        $sentences = [
            'Почему моим словам стоит доверять: 13 лет опыта',
            'Собирался рассказать о понятии формата давно',
            'Для затравки: чем плохи выпадайки в вебе, пример личного кабинета интернет-банка',
            'Понятие формата',
            'Пример 1: формат веба и формат окон настройки старых операционных систем (сравнение из старой статьи на хабре)',
            'Комментарий к статье, обращающийся к понятию формата',
            'OS/2 умер',
            'Окно настройки - почему такое? Ограничение 1: физический размер экранов',
            'Ограничение 2: размер видеопамяти',
            'Ограничение 3: частота обновления',
            'Ограничение 4: работа без драйверов',
            '640*480 - естественное ограничение в конце 90-х',
            'Особенности формата веба',
            'Сравнивать надо функциональность',
            'Бессмысленность претензий к вебу',
            'Эволюция интерфейса настройки Windows',
            'Пример 2: Одностраничные приложения',
            'Админка моего движка как пример одностраничного приложения',
            'Как бы сейчас спроектировал интерфейс админки',
            'Обсуждаем извлеченные уроки и дизайн выпадайки из личного кабинета интернет-банка',
            'Итог',
        ];

        $sourceWithCode = '<p>Ошибка <i>астатически</i> даёт более простую систему.</p>

<pre><code>&lt;?php

require \'vendor/autoload.php\';

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\ServerRequest;

// Создание экземпляра клиента Guzzle
$client = new Client();

// Обработка входящего запроса
$request = ServerRequest::fromGlobals();

// Получение URL-адреса запрашиваемого сайта
$url = $request->getUri();
$url
    // Я собирался ходить на https-сайты, поэтому подменил протокол и порт
    ->withScheme(\'https\')
    ->withPort(443)
    // Подменяем хост (видимо, тут и есть обработка протокола http-прокси)
    ->withHost($request->getHeaderLine(\'host\'))
    ->withQuery($request->getUri()->getQuery())
;
</code></pre>

<pre>123</pre>

<p><strong>Полностью жирное предложение.</strong></p>

<p><strong></strong></p>

<p><strong>Полностью курсивное предложение.</strong></p>

<p>Два <i>предложения. И оба</i> с курсивом.</p>

<p>Еще 1 раз проверим, как gt работает защита против &lt;script&gt;alert();&lt;/script&gt; xss-уязвимостей.</p>';

        $sourceWithCodeSentences = [
            'Ошибка \\iастатически\\I даёт более простую систему.',
            '<?php',
            'require \'vendor/autoload.php\';',
            'use GuzzleHttp\\\\Client;',
            'use GuzzleHttp\\\\Psr7\\\\Request;',
            'use GuzzleHttp\\\\Psr7\\\\ServerRequest;',
            '// Создание экземпляра клиента Guzzle',
            '$client = new Client();',
            '// Обработка входящего запроса',
            '$request = ServerRequest::fromGlobals();',
            '// Получение URL-адреса запрашиваемого сайта',
            '$url = $request->getUri();',
            '$url',
            '// Я собирался ходить на https-сайты, поэтому подменил протокол и порт',
            '->withScheme(\'https\')',
            '->withPort(443)',
            '// Подменяем хост (видимо, тут и есть обработка протокола http-прокси)',
            '->withHost($request->getHeaderLine(\'host\'))',
            '->withQuery($request->getUri()->getQuery())',
            ';',
            '123',
            '\\bПолностью жирное предложение.\\B',
            '\\b\\B',
            '\\bПолностью курсивное предложение.\\B',
            'Два \iпредложения.\I',
            '\iИ оба\I с курсивом.',
            'Еще 1 раз проверим, как gt работает защита против <script>alert();</script> xss-уязвимостей.',

        ];

        return [
            [new DomExtractor(), $source, $sentences],
            [new RegexExtractor(), $source, $sentences],
            [new DomExtractor(), $sourceWithCode, $sourceWithCodeSentences],
        ];
    }
}
