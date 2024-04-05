<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpComposerExtensionStubsInspection */

/**
 * @copyright 2016-2024 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test;

use Codeception\Test\Unit;
use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\Indexable;
use S2\Rose\Entity\Query;
use S2\Rose\Entity\TocEntryWithMetadata;
use S2\Rose\Finder;
use S2\Rose\Indexer;
use S2\Rose\Stemmer\PorterStemmerEnglish;
use S2\Rose\Stemmer\PorterStemmerRussian;
use S2\Rose\Storage\Database\MysqlRepository;
use S2\Rose\Storage\Database\PdoStorage;
use S2\Rose\Storage\Exception\EmptyIndexException;
use S2\Rose\Storage\File\SingleFileArrayStorage;
use S2\Rose\Storage\StorageReadInterface;
use S2\Rose\Storage\StorageWriteInterface;

/**
 * @group int
 */
class IntegrationTest extends Unit
{
    public const TEST_FILE_NUM = 17;

    /**
     * @return string
     */
    private function getTempFilename()
    {
        return __DIR__ . '/../../tmp/index2.php';
    }

    protected function _before()
    {
        @unlink($this->getTempFilename());
    }

    /**
     * @dataProvider indexableProvider
     *
     * @param Indexable[]           $indexables
     * @param StorageReadInterface  $readStorage
     * @param StorageWriteInterface $writeStorage
     *
     * @throws \Exception
     */
    public function testFeatures(
        array                 $indexables,
        StorageReadInterface  $readStorage,
        StorageWriteInterface $writeStorage
    ): void {
        $stemmer = new PorterStemmerRussian(new PorterStemmerEnglish());
        $indexer = new Indexer($writeStorage, $stemmer);

        // We're working on an empty storage
        if ($writeStorage instanceof PdoStorage) {
            $writeStorage->erase();
        }

        foreach ($indexables as $indexable) {
            $indexer->index($indexable);
        }

        if ($writeStorage instanceof SingleFileArrayStorage) {
            $writeStorage->cleanup();
            $writeStorage->save();
        }

        // Reinit storage
        if ($readStorage instanceof SingleFileArrayStorage) {
            $readStorage->load();
        }
        $finder = new Finder($readStorage, $stemmer);

        // Query 1
        $resultSet1 = $finder->find(new Query('snippets'));
        $this->assertEquals([], $resultSet1->getSortedRelevanceByExternalId(), 'Do not index description');

        // Query 2
        $resultSet2 = $finder->find(new Query('content'));

        $this->assertEquals([
            '20:id_2' => 2.5953804134970615,
            '20:id_1' => 0.12932092968696407,
            '10:id_1' => 0.08569157515491249,
        ], $resultSet2->getSortedRelevanceByExternalId());

        $items = $resultSet2->getItems();
        $this->assertEquals('id_1', $items[2]->getId());
        $this->assertEquals('10', $items[2]->getInstanceId());
        $this->assertEquals('Test page title', $items[2]->getTitle());
        $this->assertEquals('url1', $items[2]->getUrl());
        $this->assertEquals('Description can be used in snippets', $items[2]->getDescription());
        $this->assertEquals(new \DateTime('2016-08-24 00:00:00'), $items[2]->getDate());
        $this->assertEquals(0.08569157515491249, $items[2]->getRelevance());
        $this->assertEquals('I have changed the <i>content</i>.', $items[2]->getSnippet());

        $this->assertEquals(2.5953804134970615, $items[0]->getRelevance());
        $this->assertEquals(new \DateTime('2016-08-20 00:00:00+00:00'), $items[0]->getDate());
        $this->assertEquals('This is the second page to be indexed. Let\'s compose something new.', $items[0]->getSnippet(), 'No snippets due to keyword match, no description provided, first sentences are used.');

        $resultSet2 = $finder->find((new Query('content'))->setLimit(2));

        $this->assertEquals([
            '20:id_2' => 2.5953804134970615,
            '20:id_1' => 0.12932092968696407
        ], $resultSet2->getSortedRelevanceByExternalId());

        $this->assertEquals(3, $resultSet2->getTotalCount());

        $resultSet2 = $finder->find(new Query('content'));

        $resultItems = $resultSet2->getItems();
        $this->assertCount(3, $resultItems);
        $this->assertEquals(2.5953804134970615, $resultItems[0]->getRelevance(), 'Setting relevance ratio or sorting by relevance is not working');

        $resultSet2 = $finder->find(new Query('title'));
        $this->assertEquals('id_1', $resultSet2->getItems()[0]->getId());
        $this->assertEquals('Test page <i>title</i>', $resultSet2->getItems()[0]->getHighlightedTitle($stemmer));

        $resultSet2 = $finder->find((new Query('content'))->setInstanceId(10));
        $this->assertCount(1, $resultSet2->getItems());
        $this->assertEquals('id_1', $resultSet2->getItems()[0]->getId());
        $this->assertEquals(10, $resultSet2->getItems()[0]->getInstanceId());

        $resultSet2 = $finder->find((new Query('content'))->setInstanceId(20));
        $this->assertCount(2, $resultSet2->getItems());
        $this->assertEquals('id_2', $resultSet2->getItems()[0]->getId());
        $this->assertEquals(20, $resultSet2->getItems()[0]->getInstanceId());
        $this->assertEquals('id_1', $resultSet2->getItems()[1]->getId());
        $this->assertEquals(20, $resultSet2->getItems()[1]->getInstanceId());

        // Query 3
        $resultSet3 = $finder->find(new Query('сущность Plus'));
        $this->assertEquals('id_3', $resultSet3->getItems()[0]->getId());
        $this->assertEquals(
            'Тут есть тонкость - нужно проверить, как происходит экранировка в <i>сущностях</i> вроде + и &amp;<i>plus</i>;. Для этого нужно включить в текст само сочетание букв "<i>plus</i>".',
            $resultSet3->getItems()[0]->getSnippet()
        );
        $this->assertEquals(18.35150247903209, $resultSet3->getItems()[0]->getRelevance());

        // Query 4
        $resultSet4 = $finder->find(new Query('эпл'));
        $this->assertCount(1, $resultSet4->getItems());
        $this->assertEquals('id_3', $resultSet4->getItems()[0]->getId());
        $this->assertEquals(
            'Например, красно-черный, <i>эпл</i>-вотчем, и другие интересные комбинации.',
            $resultSet4->getItems()[0]->getSnippet()
        );

        $finder->setHighlightTemplate('<b>%s</b>');
        $resultSet4   = $finder->find(new Query('красный заголовку'));
        $resultItems4 = $resultSet4->getItems();
        $this->assertCount(1, $resultItems4);
        $this->assertEquals('id_3', $resultSet4->getItems()[0]->getId());
        $this->assertEquals(
            'Например, <b>красно</b>-черный, эпл-вотчем, и другие интересные комбинации.',
            $resultItems4[0]->getSnippet()
        );
        $this->assertEquals('id_3', $resultSet4->getItems()[0]->getId());
        $this->assertEquals(
            'Русский текст. <b>Красным заголовком</b>. АБВГ',
            $resultItems4[0]->getHighlightedTitle($stemmer)
        );
        $this->assertEquals(38.86779205572728, $resultSet4->getItems()[0]->getRelevance());

        // Query 5
        $resultSet5 = $finder->find(new Query('русский'));
        $this->assertCount(1, $resultSet5->getItems());
        $this->assertEquals(18.951204937870607, $resultSet5->getItems()[0]->getRelevance());

        $resultSet5 = $finder->find(new Query('русскому'));
        $this->assertCount(1, $resultSet5->getItems());
        $this->assertEquals(18.951204937870607, $resultSet5->getItems()[0]->getRelevance());

        $resultSet5 = $finder->find(new Query('абвг'));
        $this->assertCount(1, $resultSet5->getItems());
        $this->assertEquals(26.531686913018852, $resultSet5->getItems()[0]->getRelevance());

        // Query 6
        $resultSet6 = $finder->find(new Query('учитель не должен'));
        $this->assertCount(1, $resultSet6->getItems());
        $this->assertEquals(55.0961739079439, $resultSet6->getItems()[0]->getRelevance());

        // Query 7: Test empty queries
        $resultSet7 = $finder->find(new Query(''));
        $this->assertCount(0, $resultSet7->getItems());

        $resultSet7 = $finder->find(new Query('\'')); // ' must be cleared
        $this->assertCount(0, $resultSet7->getItems());

        // Query 8
        $resultSet8 = $finder->find(new Query('ціна'));
        $this->assertEquals(
            'Например, в украинском есть слово <b>ціна</b>.',
            $resultSet8->getItems()[0]->getSnippet()
        );

        // Query 9
        $resultSet9 = $finder->find(new Query('7.0'));
        $this->assertEquals(
            'Я не помню Windows 3.1, но помню Turbo Pascal <b>7.0</b>.',
            $resultSet9->getItems()[0]->getSnippet()
        );

        $resultSet9 = $finder->find(new Query('7'));
        $this->assertEquals(
            'В 1,<b>7</b> раз больше... Я не помню Windows 3.1, но помню Turbo Pascal <b>7</b>.0. Надо отдельно посмотреть, что ищется по одной цифре <b>7</b>...',
            $resultSet9->getItems()[0]->getSnippet()
        );

        $resultSet9 = $finder->find(new Query('Windows 3'));
        $this->assertEquals(
            'Я не помню <b>Windows 3</b>.1, но помню Turbo Pascal 7.0.',
            $resultSet9->getItems()[0]->getSnippet()
        );

        $resultSet9 = $finder->find(new Query('Windows 3.1'));
        $this->assertEquals(
            'Я не помню <b>Windows 3.1</b>, но помню Turbo Pascal 7.0.',
            $resultSet9->getItems()[0]->getSnippet()
        );

        $resultSet9 = $finder->find(new Query('Gallery'));
        $this->assertEquals(
            'Или что-то может называться словом <b>Gallery</b>.',
            $resultSet9->getItems()[0]->getSnippet()
        );

        $resultSet9 = $finder->find(new Query('legacy'));
        $this->assertEquals(
            'Some <b>legacy</b>. To be continued...',
            $resultSet9->getItems()[0]->getHighlightedTitle($stemmer)
        );

        // Query 10
        $resultSet10 = $finder->find(new Query('singlekeyword'));
        $this->assertCount(1, $resultSet10->getItems());
        $this->assertEquals('Description can be used in snippets', $resultSet10->getItems()[0]->getSnippet(), 'No snippets due to keyword match, description is used.');

        // Query 11
        $resultSet11 = $finder->find(new Query('images'));
        $this->assertCount(1, $resultSet11->getItems());
        $this->assertEquals('Nothing is here but <b>images</b>:', $resultSet11->getItems()[0]->getSnippet());
        $img0 = $resultSet11->getItems()[0]->getImageCollection()->offsetGet(0);
        $this->assertNotNull($img0);
        $this->assertEquals('1.jpg', $img0->getSrc());
        $this->assertEquals('10', $img0->getWidth());
        $this->assertEquals('15', $img0->getHeight());
        $this->assertEquals('', $img0->getAlt());

        $img1 = $resultSet11->getItems()[0]->getImageCollection()->offsetGet(1);
        $this->assertNotNull($img1);
        $this->assertEquals('2 3.jpg', $img1->getSrc());
        $this->assertEquals('20', $img1->getWidth());
        $this->assertEquals('25', $img1->getHeight());
        $this->assertEquals('Alternative text', $img1->getAlt());

        // Query 12
        $resultSet12 = $finder->find(new Query('long_word_with_underscores'));
        $this->assertCount(1, $resultSet12->getItems());
        $this->assertEquals('Some sentence with <b>long_word_with_underscores</b>.', $resultSet12->getItems()[0]->getSnippet());

        // Empty result
        $this->assertCount(0, $finder->find(new Query('..'))->getItems());
        $this->assertCount(0, $finder->find(new Query('...'))->getItems());

        if ($readStorage instanceof PdoStorage && strpos($GLOBALS['s2_rose_test_db']['dsn'], 'sqlite') !== 0) {
            $indexer->index(new Indexable('dummy', 'Dummy new', ''));
            $similarItems = $readStorage->getSimilar(new ExternalId('id_2', 20), false);
            $this->assertInstanceOf(TocEntryWithMetadata::class, $similarItems[0]['tocWithMetadata']);
            $this->assertEquals($right = [
                'toc_id'      => '1',
                'word_count'  => '16',
                'external_id' => 'id_1',
                'instance_id' => '10',
                'title'       => 'Test page title',
                'snippet'     => 'This is the first page to be indexed.',
                'snippet2'    => 'I have changed the content.',
            ], array_intersect_key($similarItems[0], $right));

            $similarItems = $readStorage->getSimilar(new ExternalId('id_2', 20), true);
            $this->assertEquals($right = [
                'snippet' => 'This is the first page to be <i>indexed</i>.',
            ], array_intersect_key($similarItems[0], $right));

            $similarItems = $readStorage->getSimilar(new ExternalId('id_2', 20), false, 10);
            $this->assertEquals($right = [
                'external_id' => 'id_1',
                'instance_id' => '10',
            ], array_intersect_key($similarItems[0], $right));

            $similarItems = $readStorage->getSimilar(new ExternalId('id_2', 20), false, 999);
            $this->assertCount(0, $similarItems);
        }
    }

    /**
     * @dataProvider indexableProvider
     *
     * @param Indexable[]           $indexables
     * @param StorageReadInterface  $readStorage
     * @param StorageWriteInterface $writeStorage
     *
     * @throws \RuntimeException
     */
    public function testParallelIndexingAndSearching(
        array                 $indexables,
        StorageReadInterface  $readStorage,
        StorageWriteInterface $writeStorage
    ) {
        $stemmer = new PorterStemmerRussian();
        $indexer = new Indexer($writeStorage, $stemmer);

        // We're working on an empty storage
        if ($writeStorage instanceof PdoStorage) {
            $writeStorage->erase();
        }

        $indexer->index($indexables[0]);
        if ($writeStorage instanceof SingleFileArrayStorage) {
            $writeStorage->cleanup();
            $writeStorage->save();
        }

        // Reinit storage
        if ($readStorage instanceof SingleFileArrayStorage) {
            $readStorage->load();
        }

        $finder    = new Finder($readStorage, $stemmer);
        $resultSet = $finder->find(new Query('page'));  // a word in $indexables[0]
        $this->assertCount(1, $resultSet->getItems());

        if ($writeStorage instanceof SingleFileArrayStorage) {
            // Wrap for updating the index
            $writeStorage->load();
        }
        $indexer->index($indexables[1]);
        if ($writeStorage instanceof SingleFileArrayStorage) {
            // Wrap for updating the index
            $writeStorage->cleanup();
            $writeStorage->save();
        }

        $resultSet = $finder->find(new Query('page')); // a word in $indexables[1]
        if (!($readStorage instanceof SingleFileArrayStorage)) {
            $this->assertCount(2, $resultSet->getItems());
        }

        if ($writeStorage instanceof SingleFileArrayStorage) {
            // Wrap for updating the index
            $writeStorage->load();
        }
        $indexer->removeById($indexables[1]->getExternalId()->getId(), $indexables[1]->getExternalId()->getInstanceId());
        if ($writeStorage instanceof SingleFileArrayStorage) {
            // Wrap for updating the index
            $writeStorage->cleanup();
            $writeStorage->save();
        }

        $resultSet = $finder->find(new Query('page'));
        if (!($readStorage instanceof SingleFileArrayStorage)) {
            $this->assertCount(1, $resultSet->getItems());
        }
    }

    public function testAutoErase()
    {
        global $s2_rose_test_db;
        $pdo = new \PDO($s2_rose_test_db['dsn'], $s2_rose_test_db['username'], $s2_rose_test_db['passwd']);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $pdo->exec('DROP TABLE IF EXISTS ' . 'test_' . MysqlRepository::TOC);

        $pdoStorage = new PdoStorage($pdo, 'test_');
        $stemmer    = new PorterStemmerRussian();
        $indexer    = new Indexer($pdoStorage, $stemmer);
        $indexable  = new Indexable('id_1', 'Test page title', 'This is the first page to be <i>indexed</i>. I have to make up a content.', 10);

        $e = null;
        try {
            $indexer->index($indexable);
        } catch (EmptyIndexException $e) {
        }
        $this->assertNotNull($e);

        $indexer->setAutoErase(true);
        $indexer->index($indexable);
    }

    public function indexableProvider()
    {
        $indexables = [
            (new Indexable('id_1', 'Test page title', 'This is the first page to be <i>indexed</i>. I have to make up a content.', 10))
                ->setKeywords('singlekeyword, multiple keywords')
                ->setDescription('Description can be used in snippets')
                ->setDate(new \DateTime('2016-08-24 00:00:00'))
                ->setUrl('url1')
            ,
            (new Indexable('id_2', 'Some legacy. To be continued...', 'This is the second page to be indexed. Let\'s compose something new.', 20))
                ->setKeywords('content, ')
                ->setDescription('')
                ->setDate(new \DateTime('2016-08-20 00:00:00+00:00'))
                ->setUrl('any string')
                ->setRelevanceRatio(3.14)
            ,
            (new Indexable('id_3', 'Русский текст. Красным заголовком. АБВГ', '<p>Для проверки работы нужно написать побольше слов. В 1,7 раз больше. Вот еще одно предложение.</p><p>Тут есть тонкость - нужно проверить, как происходит экранировка в сущностях вроде &plus; и &amp;plus;. Для этого нужно включить в текст само сочетание букв "plus".</p><p>Еще одна особенность - наличие слов с дефисом. Например, красно-черный, эпл-вотчем, и другие интересные комбинации. Встречаются и другие знаки препинания, например, цифры. Я не помню Windows 3.1, но помню Turbo Pascal 7.0. Надо отдельно посмотреть, что ищется по одной цифре 7... Учитель не должен допускать такого...</p><p>А еще текст бывает на других языках. Например, в украинском есть слово ціна. Или что-то может называться словом Gallery.</p>', 20))
                ->setKeywords('ключевые слова, АБВГ')
                ->setDescription('')
                ->setDate(new \DateTime('2016-08-22 00:00:00'))
                ->setUrl('/якобы.урл')
            ,
            // overwrite the previous one
            (new Indexable('id_1', 'Test page title', 'This is the first page to be <i>indexed</i>. I have changed the content.', 10))
                ->setKeywords('singlekeyword, multiple keywords')
                ->setDescription('Description can be used in snippets')
                ->setDate(new \DateTime('2016-08-24 00:00:00'))
                ->setUrl('url1')
            ,
            (new Indexable('id_1', 'Another instance', 'The same id but another instance. Word "content" is present here. Twice: content. Delimiters must be $...$ or  \[...\]', 20))
            ,
            (new Indexable('id_4', 'Another instance', 'Some sentence with long_word_with_underscores. Nothing is here but images: <img src="1.jpg" width="10" height="15"> <img src="2%203.jpg" width="20" height="25" alt="Alternative text" />', 20))
            ,
        ];

        global $s2_rose_test_db;
        $pdo = new \PDO($s2_rose_test_db['dsn'], $s2_rose_test_db['username'], $s2_rose_test_db['passwd']);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $filename = $this->getTempFilename();

        return [
            'files' => [$indexables, new SingleFileArrayStorage($filename), new SingleFileArrayStorage($filename)],
            'db'    => [$indexables, new PdoStorage($pdo, 'test_'), new PdoStorage($pdo, 'test_')],
        ];
    }
}
