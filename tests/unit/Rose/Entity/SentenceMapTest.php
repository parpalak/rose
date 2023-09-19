<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Entity;

use Codeception\Test\Unit;
use S2\Rose\Entity\Metadata\SentenceMap;
use S2\Rose\Entity\Metadata\SnippetSource;

/**
 * @group sentence
 */
class SentenceMapTest extends Unit
{
    public function testAdd(): void
    {
        $this->expectException(\LogicException::class);
        $s = new SentenceMap(SnippetSource::FORMAT_PLAIN_TEXT);
        $s->add(2, '/html/body/p[2]/text()[1]', 'Second');
        $s->add(2, '/html/body/p[2]/text()[1]', 'sentence. And a third one.');
    }

    public function testToArrayManyPaths(): void
    {
        $s = new SentenceMap(SnippetSource::FORMAT_PLAIN_TEXT);
        $s->add(1, '/html/body/p[1]/text()', 'One sentence.');
        $s->add(2, '/html/body/p[2]/text()[1]', 'Second');
        $s->add(2, '/html/body/p[2]/br', ' ');
        $s->add(2, '/html/body/p[2]/text()[2]', 'sentence. And a third one...');

        $sentenceArray = $s->toSentenceCollection()->toArray();

        $this->assertEquals([
            'One sentence.',
            'Second sentence.',
            'And a third one...',
        ], $sentenceArray);
    }

    public function testToArrayOneLargePath(): void
    {
        $s = new SentenceMap(SnippetSource::FORMAT_PLAIN_TEXT);
        $s->add(0, '', 'А это цитата, ее тоже надо индексировать. В цитате могут быть абзацы. Ошибка астатически даёт более простую систему. Еще 1 раз проверим, как gt работает защита против <script>alert();</script> xss-уязвимостей.');

        $sentenceArray = $s->toSentenceCollection()->toArray();
        $this->assertEquals([
            'А это цитата, ее тоже надо индексировать.',
            'В цитате могут быть абзацы.',
            'Ошибка астатически даёт более простую систему.',
            'Еще 1 раз проверим, как gt работает защита против <script>alert();</script> xss-уязвимостей.',
        ], $sentenceArray);
    }

    public function testToArrayOneLargePath2(): void
    {
        $s = new SentenceMap(SnippetSource::FORMAT_PLAIN_TEXT);
        $s->add(2, '/html/body/p[2]/text()[1]', 'Second sentence. And a third one...');

        $sentenceArray = $s->toSentenceCollection()->toArray();

        $this->assertEquals([
            'Second sentence.',
            'And a third one...',
        ], $sentenceArray);
    }

    public function testToArrayPathPerSentence(): void
    {
        $s = new SentenceMap(SnippetSource::FORMAT_PLAIN_TEXT);
        $s->add(2, '/html/body/p[2]/text()[1]', 'Second sentence.');
        $s->add(2, '/html/body/p[2]/br', ' ');
        $s->add(2, '/html/body/p[2]/text()[2]', 'And a third one...');

        $sentenceArray = $s->toSentenceCollection()->toArray();

        $this->assertEquals([
            'Second sentence.',
            'And a third one...',
        ], $sentenceArray);
    }
}
