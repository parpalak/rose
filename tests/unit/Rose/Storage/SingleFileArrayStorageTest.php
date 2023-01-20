<?php /** @noinspection PhpUnhandledExceptionInspection */

/**
 * @copyright 2016-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Storage;

use Codeception\Test\Unit;
use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\TocEntry;
use S2\Rose\Storage\File\SingleFileArrayStorage;

/**
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

        $storage->addEntryToToc(
            new TocEntry('test title 1', '', new \DateTime(), '', 1, '4567890lkjhgfd'),
            new ExternalId('test_id_1')
        );
        $storage->addEntryToToc(
            new TocEntry('test title 2', '', new \DateTime(), '', 1, 'edfghj8765rfg'),
            new ExternalId('test_id_2')
        );

        $entry1 = $storage->getTocByExternalId(new ExternalId('test_id_1'));
        $entry2 = $storage->getTocByExternalId(new ExternalId('test_id_2'));
        $this->assertEquals(1, $entry1->getInternalId());
        $this->assertEquals(2, $entry2->getInternalId());

        $storage->addToFulltext([1 => 'hello', 2 => 'world'], new ExternalId('test_id_1'));

        $fulltextResult = $storage->fulltextResultByWords(['hello']);
        $info           = $fulltextResult->toArray()['hello'];
        $this->assertArrayHasKey(':test_id_1', $info);
        $this->assertEquals([1], $info[':test_id_1']['pos']);

        $fulltextResult = $storage->fulltextResultByWords(['world']);
        $info           = $fulltextResult->toArray()['world'];
        $this->assertArrayHasKey(':test_id_1', $info);
        $this->assertEquals([2], $info[':test_id_1']['pos']);

        $storage->save();

        $storage = new SingleFileArrayStorage($this->getTempFilename());
        $storage->load();

        $entry1 = $storage->getTocByExternalId(new ExternalId('test_id_1'));
        $this->assertEquals('test title 1', $entry1->getTitle());
        $this->assertEquals('4567890lkjhgfd', $entry1->getHash());

        $entry3 = $storage->getTocByExternalId(new ExternalId('test_id_3'));
        $this->assertNull($entry3);

        $storage->addToFulltext([10 => 'hello', 20 => 'world'], new ExternalId('test_id_2'));

        $fulltextResult = $storage->fulltextResultByWords(['world']);
        $info           = $fulltextResult->toArray()['world'];
        $this->assertArrayHasKey(':test_id_1', $info);
        $this->assertEquals([2], $info[':test_id_1']['pos']);
        $this->assertArrayHasKey(':test_id_2', $info);
        $this->assertEquals([20], $info[':test_id_2']['pos']);

        $storage->save();
    }
}
