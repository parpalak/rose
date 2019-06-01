<?php
/**
 * @copyright 2016-2018 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test;

use Codeception\Test\Unit;
use S2\Rose\Entity\TocEntry;
use S2\Rose\Exception\UnknownIdException;
use S2\Rose\Storage\Database\PdoStorage;
use S2\Rose\Storage\Exception\EmptyIndexException;

/**
 * Class PdoStorageTest
 *
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
        $storage->removeFromToc('id_10');
        $storage->removeFromIndex('id_10');

        // Indexing
        $tocEntry1 = new TocEntry('test title', 'descr', new \DateTime('2014-05-28'), '', '123456789');
        $storage->addItemToToc($tocEntry1, 'id_1');

        $tocEntry2 = new TocEntry('', '', new \DateTime('2014-05-28'), '', 'pokjhgtyuio');
        $storage->addItemToToc($tocEntry2, 'id_2');

        $storage->addToFulltext([1 => 'word1', 2 => 'word2'], 'id_1');
        $storage->addToFulltext([1 => 'word2', 10 => 'word2'], 'id_2');

        // Searching
        $fulltextResult = $storage->fulltextResultByWords(['word1']);
        $this->assertEquals(['id_1' => [1]], $fulltextResult->toArray()['word1']);

        $fulltextResult = $storage->fulltextResultByWords(['word2']);
        $this->assertEquals(['id_1' => [2], 'id_2' => [1, 10]], $fulltextResult->toArray()['word2']);

        $entry = $storage->getTocByExternalId('id_2');
        $this->assertEquals($tocEntry2->getHash(), $entry->getHash());

        // Test updating
        $tocEntry3 = new TocEntry('', '', null, '', 'jhg678o');
        $storage->addItemToToc($tocEntry3, 'id_2');

        $entry = $storage->getTocByExternalId('id_2');
        $this->assertGreaterThan(0, $entry->getInternalId());
        $this->assertEquals($tocEntry3->getHash(), $entry->getHash());

        // Removing from index
        $storage->removeFromIndex('id_2');
        $entry = $storage->getTocByExternalId('id_2');
        $this->assertNotNull($entry);

        $storage->removeFromToc('id_2');
        $this->assertCount(0, $storage->getTocByExternalIds(['id_2']));

        $fulltextResult = $storage->fulltextResultByWords(['word2']);
        $this->assertEquals(['id_1' => [2]], $fulltextResult->toArray()['word2']);

        // Reinit and...
        $storage = new PdoStorage($this->pdo, 'test_');

        // ... make sure the cache works properly
        $this->assertCount(0, $storage->getTocByExternalIds(['id_2']));

        $fulltextResult = $storage->fulltextResultByWords(['word2']);
        $this->assertEquals(['id_1' => [2]], $fulltextResult->toArray()['word2']);

        // Remove id_1
        $entry = $storage->getTocByExternalId('id_1');
        $this->assertEquals($tocEntry1->getHash(), $entry->getHash());

        $storage->removeFromIndex('id_1');

        $entry = $storage->getTocByExternalId('id_1');
        $this->assertNotNull($entry);

        $storage->removeFromToc('id_1');
        $this->assertCount(0, $storage->getTocByExternalIds(['id_1']));
    }

    public function testParallelProcesses()
    {
        $storage = new PdoStorage($this->pdo, 'test_');
        $storage->erase();

        $storage2 = new PdoStorage($this->pdo, 'test_');
        $storage2->getTocSize(); // Caching TOC

        // Indexing
        $tocEntry1 = new TocEntry('test title', 'descr', new \DateTime('2014-05-28'), '', '123456789');
        $storage->addItemToToc($tocEntry1, 'id_1');

        // Race condition
        $tocEntry1mod = new TocEntry('test title', 'descr', new \DateTime('2014-05-28'), '', '9654321');
        $storage2->addItemToToc($tocEntry1mod, 'id_1');
    }

    public function testAddToSingleKeywordIndex()
    {
        $storage = new PdoStorage($this->pdo, 'test_');
        $storage->erase();

        $tocEntry = new TocEntry('test title', 'descr', new \DateTime('2014-05-28'), '', '123456789');
        $storage->addItemToToc($tocEntry, 'id_1');

        $tocEntry2 = new TocEntry('test title', 'descr', new \DateTime('2014-05-28'), '', '123456789');
        $storage->addItemToToc($tocEntry2, 'id_2');

        $tocEntry3 = new TocEntry('test title', 'descr', new \DateTime('2014-05-28'), '', '123456789');
        $storage->addItemToToc($tocEntry3, 'id_3');

        $storage->addToSingleKeywordIndex('type1', 'id_1', 1);
        $storage->addToSingleKeywordIndex('type2', 'id_1', 2);
        $storage->addToSingleKeywordIndex('type1', 'id_2', 1);
        $storage->addToSingleKeywordIndex('type1', 'id_3', 1);
        $storage->addToSingleKeywordIndex('type1-1', 'id_1', 1);

        $data = $storage->getSingleKeywordIndexByWords(['type1', 'type2']);
        $this->assertCount(2, $data);
        $this->assertCount(3, $data['type1']);
        $this->assertEquals(1, $data['type1']['id_1']);
        $this->assertEquals(2, $data['type2']['id_1']);
    }

    public function testTransactions()
    {
        // This test should lock on SELECT query, not INSERT.
        // How this could be tested automatically?
        return;
        global $s2_rose_test_db;

        $pdo2 = new \PDO($s2_rose_test_db['dsn'], $s2_rose_test_db['username'], $s2_rose_test_db['passwd']);
        $pdo2->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $storage = new PdoStorage($this->pdo, 'test_tr_');
        $storage->erase();

        $storage->startTransaction();
        $storage->addItemToToc(
            new TocEntry('title 1', 'descr 1', new \DateTime('2014-05-28'), '', '123456789'),
            'id_1'
        );
        $storage->addToFulltext(['word1', 'word2', 'word3'], 'id_1');

        $storage2 = new PdoStorage($pdo2, 'test_tr_');
        $storage2->startTransaction();
        $storage2->addItemToToc(
            new TocEntry('title 2', 'descr 2', new \DateTime('2014-05-28'), '', 'qwerty'),
            'id_2'
        );
        $storage2->addToFulltext(['word1', 'word5'], 'id_2');
        $storage2->commitTransaction();

        $storage->addToFulltext(['word4', 'word5', 'word6'], 'id_1');
        $storage->commitTransaction();
    }

    public function testBrokenDb()
    {
        $this->expectException(EmptyIndexException::class);

        $storage = new PdoStorage($this->pdo, 'test_');
        $storage->erase();

        $storage->addItemToToc(
            new TocEntry('title 1', 'descr 1', new \DateTime('2014-05-28'), '', '123456789'),
            'id_1'
        );

        $this->pdo->exec('DROP TABLE test_keyword_multiple_index;');

        $storage->removeFromIndex('id_1');
    }

    public function testNonExistentDbAddToToc()
    {
        $this->expectException(EmptyIndexException::class);

        $storage   = new PdoStorage($this->pdo, 'non_existent_');
        $tocEntry1 = new TocEntry('test title', 'descr', new \DateTime('2014-05-28'), '', '123456789');
        $storage->addItemToToc($tocEntry1, 'id_1');
    }

    public function testNonExistentDbAddToFulltext()
    {
        $this->expectException(UnknownIdException::class);
        $storage = new PdoStorage($this->pdo, 'non_existent_');
        $storage->addToFulltext(['word'], 'id_1');
    }

    public function testNonExistentDbAddToSingleKeywordIndex()
    {
        $this->expectException(UnknownIdException::class);
        $storage = new PdoStorage($this->pdo, 'non_existent_');
        $storage->addToSingleKeywordIndex('keyword', 'id_1', 1);
    }

    public function testNonExistentDbAddToMultipleKeywordIndex()
    {
        $this->expectException(UnknownIdException::class);
        $storage = new PdoStorage($this->pdo, 'non_existent_');
        $storage->addToMultipleKeywordIndex('multi keyword', 'id_1', 1);
    }

    public function testNonExistentDbGetTocByExternalIds()
    {
        $this->expectException(EmptyIndexException::class);
        $storage = new PdoStorage($this->pdo, 'non_existent_');
        $storage->getTocByExternalIds(['id_1'])['id_1'];
    }

    public function testNonExistentDbFindTocByTitle()
    {
        $this->expectException(EmptyIndexException::class);
        $storage = new PdoStorage($this->pdo, 'non_existent_');
        $storage->findTocByTitle('title');
    }

    public function testNonExistentDbGetTocSize()
    {
        $this->expectException(EmptyIndexException::class);
        $storage = new PdoStorage($this->pdo, 'non_existent_');
        $storage->getTocSize();
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
        $storage->removeFromToc('id_1');
    }

    public function testNonExistentDbRemoveFromIndex()
    {
        $this->expectException(EmptyIndexException::class);
        $storage = new PdoStorage($this->pdo, 'non_existent_');
        $storage->removeFromIndex('id_1');
    }
}
