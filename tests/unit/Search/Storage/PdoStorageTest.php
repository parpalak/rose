<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Search\Test;

use Codeception\Test\Unit;
use S2\Search\Entity\TocEntry;
use S2\Search\Storage\Database\PdoStorage;

/**
 * Class PdoStorageTest
 *
 * @group storage
 */
class PdoStorageTest extends Unit
{
	/**
	 * @var \PDO
	 */
	protected $pdo;

	public function _before()
	{
		global $s2_search_test_db;

		$this->pdo = new \PDO($s2_search_test_db['dsn'], $s2_search_test_db['username'], $s2_search_test_db['passwd']);
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
}
