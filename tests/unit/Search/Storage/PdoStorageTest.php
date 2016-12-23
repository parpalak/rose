<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test;

use Codeception\Test\Unit;
use S2\Rose\Entity\TocEntry;
use S2\Rose\Storage\Database\PdoStorage;

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
		$data1 = $storage->getFulltextByWord('word1');
		$this->assertEquals(['id_1' => [1]], $data1);

		$data2 = $storage->getFulltextByWord('word2');
		$this->assertEquals(['id_1' => [2], 'id_2' => [1, 10]], $data2);

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
		$entry = $storage->getTocByExternalId('id_2');
		$this->assertNull($entry);

		$data2 = $storage->getFulltextByWord('word2');
		$this->assertEquals(['id_1' => [2]], $data2);

		// Reinit and...
		$storage = new PdoStorage($this->pdo, 'test_');

		// ... make sure the cache works properly
		$entry = $storage->getTocByExternalId('id_2');
		$this->assertNull($entry);

		$data2 = $storage->getFulltextByWord('word2');
		$this->assertEquals(['id_1' => [2]], $data2);

		// Remove id_1
		$entry = $storage->getTocByExternalId('id_1');
		$this->assertEquals($tocEntry1->getHash(), $entry->getHash());

		$storage->removeFromIndex('id_1');

		$entry = $storage->getTocByExternalId('id_1');
		$this->assertNotNull($entry);

		$storage->removeFromToc('id_1');
		$entry = $storage->getTocByExternalId('id_1');
		$this->assertNull($entry);
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

	/**
	 * @expectedException \S2\Rose\Storage\Exception\EmptyIndexException
	 */
	public function testNonExistentDbAddToToc()
	{
		$storage   = new PdoStorage($this->pdo, 'non_existent_');
		$tocEntry1 = new TocEntry('test title', 'descr', new \DateTime('2014-05-28'), '', '123456789');
		$storage->addItemToToc($tocEntry1, 'id_1');
	}

	/**
	 * @expectedException \S2\Rose\Storage\Exception\EmptyIndexException
	 */
	public function testNonExistentDbAddToFulltext()
	{
		$storage = new PdoStorage($this->pdo, 'non_existent_');
		$storage->addToFulltext(['word'], 'id_1');
	}

	/**
	 * @expectedException \S2\Rose\Storage\Exception\EmptyIndexException
	 */
	public function testNonExistentDbAddToSingleKeywordIndex()
	{
		$storage = new PdoStorage($this->pdo, 'non_existent_');
		$storage->addToSingleKeywordIndex('keyword', 'id_1', 1);
	}

	/**
	 * @expectedException \S2\Rose\Storage\Exception\EmptyIndexException
	 */
	public function testNonExistentDbAddToMultipleKeywordIndex()
	{
		$storage = new PdoStorage($this->pdo, 'non_existent_');
		$storage->addToMultipleKeywordIndex('multi keyword', 'id_1', 1);
	}

	/**
	 * @expectedException \S2\Rose\Storage\Exception\EmptyIndexException
	 */
	public function testNonExistentDbGetTocByExternalId()
	{
		$storage = new PdoStorage($this->pdo, 'non_existent_');
		$storage->getTocByExternalId('id_1');
	}

	/**
	 * @expectedException \S2\Rose\Storage\Exception\EmptyIndexException
	 */
	public function testNonExistentDbFindTocByTitle()
	{
		$storage = new PdoStorage($this->pdo, 'non_existent_');
		$storage->findTocByTitle('title');
	}

	/**
	 * @expectedException \S2\Rose\Storage\Exception\EmptyIndexException
	 */
	public function testNonExistentDbGetTocSize()
	{
		$storage = new PdoStorage($this->pdo, 'non_existent_');
		$storage->getTocSize();
	}

	/**
	 * @expectedException \S2\Rose\Storage\Exception\EmptyIndexException
	 */
	public function testNonExistentDbGetFulltextByWord()
	{
		$storage = new PdoStorage($this->pdo, 'non_existent_');
		$storage->getFulltextByWord('word');
	}

	/**
	 * @expectedException \S2\Rose\Storage\Exception\EmptyIndexException
	 */
	public function testNonExistentDbGetSingleKeywordIndexByString()
	{
		$storage = new PdoStorage($this->pdo, 'non_existent_');
		$storage->getSingleKeywordIndexByWord('keyword');
	}

	/**
	 * @expectedException \S2\Rose\Storage\Exception\EmptyIndexException
	 */
	public function testNonExistentDbGetMultipleKeywordIndexByString()
	{
		$storage = new PdoStorage($this->pdo, 'non_existent_');
		$storage->getMultipleKeywordIndexByString('multi keyword');
	}

	/**
	 * @expectedException \S2\Rose\Storage\Exception\EmptyIndexException
	 */
	public function testNonExistentDbRemoveFromToc()
	{
		$storage = new PdoStorage($this->pdo, 'non_existent_');
		$storage->removeFromToc('id_1');
	}

	/**
	 * @expectedException \S2\Rose\Storage\Exception\EmptyIndexException
	 */
	public function testNonExistentDbRemoveFromIndex()
	{
		$storage = new PdoStorage($this->pdo, 'non_existent_');
		$storage->removeFromIndex('id_1');
	}
}
