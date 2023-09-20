<?php /** @noinspection PhpUnhandledExceptionInspection */

/**
 * @copyright 2016-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Entity;

use Codeception\Test\Unit;
use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\Metadata\SnippetSource;
use S2\Rose\Entity\ResultSet;
use S2\Rose\Entity\Snippet;
use S2\Rose\Entity\SnippetLine;
use S2\Rose\Exception\ImmutableException;
use S2\Rose\Exception\UnknownIdException;

/**
 * @group entity
 * @group result
 */
class ResultSetTest extends Unit
{
    public function testLimit()
    {
        $result = $this->prepareResult(new ResultSet());
        $data   = $result->getSortedRelevanceByExternalId();
        $this->assertCount(30, $data);

        $result = $this->prepareResult(new ResultSet(2));
        $data   = $result->getSortedRelevanceByExternalId();
        $this->assertCount(2, $data);
        $this->assertEquals(30, $result->getTotalCount());
        $this->assertEquals(39, $data[':id_29']);
        $this->assertEquals(38, $data[':id_28']);

        $result = $this->prepareResult(new ResultSet(4, 3));
        $data   = $result->getSortedRelevanceByExternalId();
        $this->assertCount(4, $data);
        $this->assertEquals(30, $result->getTotalCount());
        $this->assertEquals(36, $data[':id_26']);
        $this->assertEquals(35, $data[':id_25']);
        $this->assertEquals(34, $data[':id_24']);
        $this->assertEquals(33, $data[':id_23']);
    }

    public function testEmpty()
    {
        $resultSet = new ResultSet();
        $resultSet->freeze();
        $data = $resultSet->getItems();
        $this->assertCount(0, $data);
    }

    public function testNotFrozenGetItems()
    {
        $this->expectException(ImmutableException::class);
        $resultSet = new ResultSet();
        $resultSet->getItems();
    }

    public function testNotFrozenAttachSnippet()
    {
        $this->expectException(UnknownIdException::class);
        $resultSet = new ResultSet();
        $resultSet->attachSnippet(new ExternalId('not found'), new Snippet(0, '<i>%s</i>', new SnippetLine('', SnippetSource::FORMAT_PLAIN_TEXT, [], 0.0)));
    }

    public function testNotFrozenGetFoundExternalIds()
    {
        $this->expectException(ImmutableException::class);
        $resultSet = new ResultSet();
        $resultSet->getFoundExternalIds();
    }

    public function testNotFrozenGetFoundWordsByExternalId()
    {
        $this->expectException(ImmutableException::class);
        $resultSet = new ResultSet();
        $resultSet->getFoundWordPositionsByExternalId();
    }

    public function testNotFrozenGetSortedExternalIds()
    {
        $this->expectException(ImmutableException::class);
        $resultSet = new ResultSet();
        $resultSet->getSortedExternalIds();
    }

    public function testNotFrozenGetSortedRelevanceByExternalId()
    {
        $this->expectException(ImmutableException::class);
        $resultSet = new ResultSet();
        $resultSet->getSortedRelevanceByExternalId();
    }

    /**
     * @param ResultSet $result
     *
     * @return ResultSet
     * @throws ImmutableException
     * @throws \S2\Rose\Exception\InvalidArgumentException
     */
    private function prepareResult(ResultSet $result)
    {
        for ($i = 30; $i--;) {
            $externalId = new ExternalId('id_' . $i);
            $result->addWordWeight('test1', $externalId, ['test' => $i]);
            $result->addWordWeight('test2', $externalId, ['test' => 10]);
        }

        $result->freeze();

        return $result;
    }
}
