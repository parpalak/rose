<?php /** @noinspection PhpUnhandledExceptionInspection */

/**
 * @copyright 2016-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Entity;

use Codeception\Test\Unit;
use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\ResultSet;
use S2\Rose\Entity\Snippet;
use S2\Rose\Exception\ImmutableException;
use S2\Rose\Exception\InvalidArgumentException;
use S2\Rose\Exception\RuntimeException;
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
        $this->assertEquals(39, $data[':id_29']);
        $this->assertEquals(38, $data[':id_28']);

        $result = $this->prepareResult(new ResultSet(4, 3));
        $data   = $result->getSortedRelevanceByExternalId();
        $this->assertCount(4, $data);
        $this->assertEquals(36, $data[':id_26']);
        $this->assertEquals(35, $data[':id_25']);
        $this->assertEquals(34, $data[':id_24']);
        $this->assertEquals(33, $data[':id_23']);
    }

    public function testSetRelevanceInvalidExternalId()
    {
        $this->expectException(UnknownIdException::class);
        $result = $this->prepareResult(new ResultSet());
        $result->setRelevanceRatio(new ExternalId('not_found'), 2);
    }

    public function testSetRelevanceInvalidRatio()
    {
        $this->expectException(InvalidArgumentException::class);
        $result = $this->prepareResult(new ResultSet());
        $result->setRelevanceRatio(new ExternalId('id_10'), ['not a number']);
    }

    public function testSetRelevance()
    {
        $result           = $this->prepareResult(new ResultSet(2));
        $foundExternalIds = $result->getFoundExternalIds()->toArray();
        $this->assertCount(30, $foundExternalIds);
        $this->assertEquals('id_29', $foundExternalIds[0]->getId());
        $this->assertEquals('id_10', $foundExternalIds[19]->getId());

        $result->setRelevanceRatio(new ExternalId('id_10'), 2.7);
        $result->setRelevanceRatio(new ExternalId('id_29'), '1.1');

        $data = $result->getSortedRelevanceByExternalId();

        $this->assertCount(2, $data);
        $this->assertEquals(':id_10', array_keys($data)[0]);
        $this->assertEquals((10 + 10) * 2.7, $data[':id_10']);
        $this->assertEquals((10 + 29) * 1.1, $data[':id_29']);
    }

    public function testNoSetRelevanceAfterSorting()
    {
        $this->expectException(ImmutableException::class);
        $this->expectExceptionMessage('One cannot set relevance ratios after sorting the result set.');

        $result           = $this->prepareResult(new ResultSet(2));
        $foundExternalIds = $result->getFoundExternalIds()->toArray();
        $this->assertEquals('id_10', $foundExternalIds[19]->getId());

        $result->setRelevanceRatio(new ExternalId('id_10'), 2.72);

        $data = $result->getSortedRelevanceByExternalId();

        $result->setRelevanceRatio(new ExternalId('id_10'), 3.14);
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
        $resultSet->attachSnippet(new ExternalId('not found'), new Snippet('', '', '<i>%s</i>'));
    }

    public function testNotFrozenGetFoundExternalIds()
    {
        $this->expectException(ImmutableException::class);
        $resultSet = new ResultSet();
        $resultSet->getFoundExternalIds();
    }

    public function testNotFrozenSetRelevanceRatio()
    {
        $this->expectException(ImmutableException::class);
        $resultSet = new ResultSet();
        $resultSet->setRelevanceRatio(new ExternalId('not found'), 2);
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
            $result->addWordWeight('test1', $externalId, $i);
            $result->addWordWeight('test2', $externalId, 10);
        }

        $result->freeze();

        return $result;
    }
}
