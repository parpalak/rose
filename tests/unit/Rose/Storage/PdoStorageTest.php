<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpComposerExtensionStubsInspection */

/**
 * @copyright 2016-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test;

use Codeception\Test\Unit;
use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\ExternalIdCollection;
use S2\Rose\Entity\TocEntry;
use S2\Rose\Exception\UnknownIdException;
use S2\Rose\Storage\Database\PdoStorage;
use S2\Rose\Storage\Exception\EmptyIndexException;

/**
 * @group storage
 * @group pdo
 */
class PdoStorageTest extends Unit
{
    /**
     * @var \PDO
     */
    protected $pdo;

    public function _before()
    {
        global $s2_rose_test_db;

        $this->pdo = new \PDO($s2_rose_test_db['dsn'], $s2_rose_test_db['username'], $s2_rose_test_db['passwd']);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function testStorage()
    {
        $storage = new PdoStorage($this->pdo, 'test_');
        $storage->erase();

        // Removing non-existent items
        $storage->removeFromToc(new ExternalId('id_10'));
        $storage->removeFromIndex(new ExternalId('id_10'));

        // Indexing
        $externalId1 = new ExternalId('id_1', 1);
        $externalId2 = new ExternalId('id_2', 2);

        $tocEntry1 = new TocEntry('test title', 'descr', new \DateTime('2014-05-28'), '', '123456789');
        $storage->addEntryToToc($tocEntry1, $externalId1);

        $tocEntry2 = new TocEntry('', '', new \DateTime('2014-05-28'), '', 'pokjhgtyuio');
        $storage->addEntryToToc($tocEntry2, $externalId2);

        $storage->addToFulltext([1 => 'word1', 2 => 'word2'], $externalId1);
        $storage->addToFulltext([1 => 'word2', 10 => 'word2'], $externalId2);

        // Searching
        $fulltextResult = $storage->fulltextResultByWords(['word1']);
        $this->assertEquals(['1:id_1' => ['pos' => [1], 'extId' => new ExternalId('id_1', 1)]], $fulltextResult->toArray()['word1']);

        $fulltextResult = $storage->fulltextResultByWords(['word2']);
        $this->assertEquals([
            '1:id_1' => ['pos' => ['2'], 'extId' => new ExternalId('id_1', 1)],
            '2:id_2' => ['pos' => [1, 10], 'extId' => new ExternalId('id_2', 2)],
        ], $fulltextResult->toArray()['word2']);

        $fulltextResult = $storage->fulltextResultByWords(['word2'], 1);
        $this->assertEquals([
            '1:id_1' => ['pos' => ['2'], 'extId' => new ExternalId('id_1', 1)],
        ], $fulltextResult->toArray()['word2']);

        $fulltextResult = $storage->fulltextResultByWords(['word2'], 2);
        $this->assertEquals([
            '2:id_2' => ['pos' => [1, 10], 'extId' => new ExternalId('id_2', 2)],
        ], $fulltextResult->toArray()['word2']);

        $entry = $storage->getTocByExternalId($externalId2);
        $this->assertEquals($tocEntry2->getHash(), $entry->getHash());

        // Test updating
        $tocEntry3 = new TocEntry('', '', null, '', 'jhg678o');
        $storage->addEntryToToc($tocEntry3, $externalId2);

        $entry = $storage->getTocByExternalId($externalId2);
        $this->assertGreaterThan(0, $entry->getInternalId());
        $this->assertEquals($tocEntry3->getHash(), $entry->getHash());

        // Removing from index
        $storage->removeFromIndex($externalId2);
        $entry = $storage->getTocByExternalId($externalId2);
        $this->assertNotNull($entry);

        $storage->removeFromToc($externalId2);
        $this->assertCount(0, $storage->getTocByExternalIds(new ExternalIdCollection([$externalId2])));

        $fulltextResult = $storage->fulltextResultByWords(['word2']);
        $this->assertEquals([
            '1:id_1' => ['pos' => ['2'], 'extId' => new ExternalId('id_1', 1)],
        ], $fulltextResult->toArray()['word2']);

        // Reinit and...
        $storage = new PdoStorage($this->pdo, 'test_');

        // ... make sure the cache works properly
        $this->assertCount(0, $storage->getTocByExternalIds(new ExternalIdCollection([$externalId2])));

        $fulltextResult = $storage->fulltextResultByWords(['word2']);
        $this->assertEquals([
            '1:id_1' => ['pos' => ['2'], 'extId' => new ExternalId('id_1', 1)],
        ], $fulltextResult->toArray()['word2']);

        // Remove id_1
        $entry = $storage->getTocByExternalId($externalId1);
        $this->assertEquals($tocEntry1->getHash(), $entry->getHash());

        $storage->removeFromIndex($externalId1);

        $entry = $storage->getTocByExternalId($externalId1);
        $this->assertNotNull($entry);

        $storage->removeFromToc($externalId1);
        $this->assertCount(0, $storage->getTocByExternalIds(new ExternalIdCollection([$externalId1])));
    }

    public function testParallelProcesses()
    {
        $storage = new PdoStorage($this->pdo, 'test_');
        $storage->erase();

        $storage2 = new PdoStorage($this->pdo, 'test_');

        $externalId = new ExternalId('id_1');

        // Indexing
        $tocEntry1 = new TocEntry('test title', 'descr', new \DateTime('2014-05-28'), '', '123456789');
        $storage->addEntryToToc($tocEntry1, $externalId);

        // Race condition
        $tocEntry1mod = new TocEntry('test title', 'descr', new \DateTime('2014-05-28'), '', '9654321');
        $storage2->addEntryToToc($tocEntry1mod, $externalId);

        $tocEntry2mod = new TocEntry('test title', 'descr', new \DateTime('2014-05-28'), '', '111111');
        $storage->addEntryToToc($tocEntry2mod, $externalId);

        $this->assertEquals('111111', $storage2->getTocByExternalId($externalId)->getHash());
    }

    public function testAddToSingleKeywordIndex()
    {
        $storage = new PdoStorage($this->pdo, 'test_');
        $storage->erase();

        $tocEntry = new TocEntry('test title', 'descr', new \DateTime('2014-05-28'), '', '123456789');
        $storage->addEntryToToc($tocEntry, new ExternalId('id_1'));

        $tocEntry2 = new TocEntry('test title', 'descr', new \DateTime('2014-05-28'), '', '123456789');
        $storage->addEntryToToc($tocEntry2, new ExternalId('id_2'));

        $tocEntry3 = new TocEntry('test title', 'descr', new \DateTime('2014-05-28'), '', '123456789');
        $storage->addEntryToToc($tocEntry3, new ExternalId('id_3'));

        $storage->addToSingleKeywordIndex('type1', new ExternalId('id_1'), 1);
        $storage->addToSingleKeywordIndex('type2', new ExternalId('id_1'), 2);
        $storage->addToSingleKeywordIndex('type1', new ExternalId('id_2'), 1);
        $storage->addToSingleKeywordIndex('type1', new ExternalId('id_3'), 1);
        $storage->addToSingleKeywordIndex('type1-1', new ExternalId('id_1'), 1);

        $data = $storage->getSingleKeywordIndexByWords(['type1', 'type2']);
        $this->assertCount(2, $data);

        $result = [];
        $data['type1']->iterate(static function (ExternalId $externalId, $type) use (&$result) {
            $result[] = [$externalId, $type];
        });
        $this->assertCount(3, $result);
        $this->assertEquals('id_1', $result[0][0]->getId());
        $this->assertEquals(1, $result[0][1]);

        $result = [];
        $data['type2']->iterate(static function (ExternalId $externalId, $type) use (&$result) {
            $result[] = [$externalId, $type];
        });
        $this->assertCount(1, $result);
        $this->assertEquals('id_1', $result[0][0]->getId());
        $this->assertEquals(2, $result[0][1]);
    }

    public function testTransactions()
    {
        // This test should lock on INSERT query.
        // How this could be tested automatically?
        return;
        global $s2_rose_test_db;

        $pdo2 = new \PDO($s2_rose_test_db['dsn'], $s2_rose_test_db['username'], $s2_rose_test_db['passwd']);
        $pdo2->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $storage = new PdoStorage($this->pdo, 'test_tr_');
        $storage->erase();

        $storage->startTransaction();
        $storage->addEntryToToc(
            new TocEntry('title 1', 'descr 1', new \DateTime('2014-05-28'), '', '123456789'),
            new ExternalId('id_1')
        );
        $storage->addToFulltext(['word1', 'word2', 'word3'], new ExternalId('id_1'));

        $storage2 = new PdoStorage($pdo2, 'test_tr_');
        $storage2->startTransaction();
        $storage2->addEntryToToc(
            new TocEntry('title 2', 'descr 2', new \DateTime('2014-05-28'), '', 'qwerty'),
            new ExternalId('id_2')
        );
        $storage2->addToFulltext(['word1', 'word5'], new ExternalId('id_2'));
        $storage2->commitTransaction();

        $storage->addToFulltext(['word4', 'word5', 'word6'], new ExternalId('id_1'));
        $storage->commitTransaction();
    }

    public function testBrokenDb()
    {
        $this->expectException(EmptyIndexException::class);

        $storage = new PdoStorage($this->pdo, 'test_');
        $storage->erase();

        $storage->addEntryToToc(
            new TocEntry('title 1', 'descr 1', new \DateTime('2014-05-28'), '', '123456789'),
            new ExternalId('id_1')
        );

        $this->pdo->exec('DROP TABLE test_keyword_multiple_index;');

        $storage->removeFromIndex(new ExternalId('id_1'));
    }

    public function testNonExistentDbAddToToc()
    {
        $this->expectException(EmptyIndexException::class);

        $storage   = new PdoStorage($this->pdo, 'non_existent_');
        $tocEntry1 = new TocEntry('test title', 'descr', new \DateTime('2014-05-28'), '', '123456789');
        $storage->addEntryToToc($tocEntry1, new ExternalId('id_1'));
    }

    public function testNonExistentDbAddToFulltext()
    {
        $this->expectException(UnknownIdException::class);
        $storage = new PdoStorage($this->pdo, 'non_existent_');
        $storage->addToFulltext(['word'], new ExternalId('id_1'));
    }

    public function testNonExistentDbAddToSingleKeywordIndex()
    {
        $this->expectException(UnknownIdException::class);
        $storage = new PdoStorage($this->pdo, 'non_existent_');
        $storage->addToSingleKeywordIndex('keyword', new ExternalId('id_1'), 1);
    }

    public function testNonExistentDbAddToMultipleKeywordIndex()
    {
        $this->expectException(UnknownIdException::class);
        $storage = new PdoStorage($this->pdo, 'non_existent_');
        $storage->addToMultipleKeywordIndex('multi keyword', new ExternalId('id_1'), 1);
    }

    public function testNonExistentDbGetTocByExternalIds()
    {
        $this->expectException(EmptyIndexException::class);
        $storage = new PdoStorage($this->pdo, 'non_existent_');
        $storage->getTocByExternalIds(new ExternalIdCollection([new ExternalId('id_1')]));
    }

    public function testNonExistentDbGetTocSize()
    {
        $this->expectException(EmptyIndexException::class);
        $storage = new PdoStorage($this->pdo, 'non_existent_');
        $storage->getTocSize(null);
    }

    public function testNonExistentDbFillFulltextResultForWords()
    {
        $this->expectException(EmptyIndexException::class);
        $storage = new PdoStorage($this->pdo, 'non_existent_');
        $storage->fulltextResultByWords(['word']);
    }

    public function testNonExistentDbGetSingleKeywordIndexByString()
    {
        $this->expectException(EmptyIndexException::class);
        $storage = new PdoStorage($this->pdo, 'non_existent_');
        $storage->getSingleKeywordIndexByWords(['keyword']);
    }

    public function testNonExistentDbGetMultipleKeywordIndexByString()
    {
        $this->expectException(EmptyIndexException::class);
        $storage = new PdoStorage($this->pdo, 'non_existent_');
        $storage->getMultipleKeywordIndexByString('multi keyword');
    }

    public function testNonExistentDbRemoveFromToc()
    {
        $this->expectException(EmptyIndexException::class);
        $storage = new PdoStorage($this->pdo, 'non_existent_');
        $storage->removeFromToc(new ExternalId('id_1'));
    }

    public function testNonExistentDbRemoveFromIndex()
    {
        $this->expectException(EmptyIndexException::class);
        $storage = new PdoStorage($this->pdo, 'non_existent_');
        $storage->removeFromIndex(new ExternalId('id_1'));
    }
}
