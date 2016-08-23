<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Search\Test;

use Codeception\Test\Unit;
use S2\Search\Entity\TocEntry;
use S2\Search\Storage\File\SingleFileArrayStorage;

/**
 * Class SingleFileArrayStorageTest
 *
 * @group storage
 */
class SingleFileArrayStorageTest extends Unit
{
	/**
	 * @return string
	 */
	private function getTempFilename()
	{
		return __DIR__ . '/../../../tmp/storage_test.php';
	}

	protected function _before()
	{
		@unlink($this->getTempFilename());
	}

	public function testStorage()
	{
		$storage = new SingleFileArrayStorage($this->getTempFilename());

		$storage->load();

		$storage->addItemToToc(
			new TocEntry('test title 1', '', new \DateTime(), '', '4567890lkjhgfd'),
			'test_id_1'
		);
		$storage->addItemToToc(
			new TocEntry('test title 2', '', new \DateTime(), '', 'edfghj8765rfg'),
			'test_id_2'
		);

		$entry1 = $storage->getTocByExternalId('test_id_1');
		$entry2 = $storage->getTocByExternalId('test_id_2');
		$this->assertEquals(1, $entry1->getInternalId());
		$this->assertEquals(2, $entry2->getInternalId());

		$storage->addToFulltext('hello', 'test_id_1', 1);
		$storage->addToFulltext('world', 'test_id_1', 2);

		$info = $storage->getFulltextByWord('hello');
		$this->assertArrayHasKey('test_id_1', $info);
		$this->assertEquals([1], $info['test_id_1']);

		$info = $storage->getFulltextByWord('world');
		$this->assertArrayHasKey('test_id_1', $info);
		$this->assertEquals([2], $info['test_id_1']);

		$storage->save();

		$storage = new SingleFileArrayStorage($this->getTempFilename());
		$storage->load();

		$entry1 = $storage->getTocByExternalId('test_id_1');
		$this->assertEquals('test title 1', $entry1->getTitle());
		$this->assertEquals('4567890lkjhgfd', $entry1->getHash());

		$entry3 = $storage->getTocByExternalId('test_id_3');
		$this->assertNull($entry3);

		$storage->addToFulltext('hello', 'test_id_2', 10);
		$storage->addToFulltext('world', 'test_id_2', 20);

		$info = $storage->getFulltextByWord('world');
		$this->assertArrayHasKey('test_id_1', $info);
		$this->assertEquals([2], $info['test_id_1']);
		$this->assertArrayHasKey('test_id_2', $info);
		$this->assertEquals([20], $info['test_id_2']);

		$storage->save();
	}
}
