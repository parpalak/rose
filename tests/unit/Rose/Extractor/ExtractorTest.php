<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Extractor;

use Codeception\Test\Unit;
use S2\Rose\Extractor\HtmlDom\DomExtractor;
use S2\Rose\Extractor\HtmlRegex\RegexExtractor;

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

        self::assertEquals($resultText, $sentenceMap->toSentenceCollection()->getText());
    }

    /**
     * @dataProvider htmlTextProvider
     */
    public function testDomExtractor(string $htmlText, string $resultText, ?array $s = null): void
    {
        $extractionResult = $this->domExtractor->extract($htmlText);
        $sentenceMap      = $extractionResult->getContentWithMetadata()->getSentenceMap();

        self::assertEquals($resultText, $sentenceMap->toSentenceCollection()->getText());
        if ($s !== null) {
            self::assertEquals($s, $sentenceMap->toSentenceCollection()->getWordsArray());
        }
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
            ['<P><i>This</i> sentence is a little bit <em>longer. And</em> this is not.</p>', 'This sentence is a little bit longer. And this is not.'],
            [
                '<P><i>This</i> sentence&nbsp;contains entities like &#43;, &plus;, &planck;, &amp;, &lt;, &quot;, &#8212;, &laquo;, &#x2603;, &#x1D306;, &#xA9;, &copy;. &amp;plus; is not an entity.</p>',
                'This sentence contains entities like +, +, ℏ, &, <, ", —, «, ☃, 𝌆, ©, ©. &plus; is not an entity.',
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

<p><img src="2.jpg" width="300" height="200">

<blockquote>
    А это цитата, ее тоже надо индексировать.
    <p>В цитате могут быть абзацы.</p>
</blockquote>

<img src="3.jpg" width="300" height="200">

<p>Ошибка <i>астатически</i> даёт более простую систему.</p>

<p>Еще 1 раз проверим, как gt работает защита против &lt;script&gt;alert();&lt;/script&gt; xss-уязвимостей.</p>',
                'Должно проиндексироваться. Внешнее кольцо позволяет пренебречь. А это цитата, ее тоже надо индексировать. В цитате могут быть абзацы. Ошибка астатически даёт более простую систему. Еще 1 раз проверим, как gt работает защита против <script>alert();</script> xss-уязвимостей.',
            ],
        ];
    }
}
