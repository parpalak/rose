<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test\Entity;

use Codeception\Test\Unit;
use S2\Rose\Entity\ResultSet;
use S2\Rose\Entity\Snippet;

/**
 * Class ResultTest
 *
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
		$this->assertEquals(39, $data['id_29']);
		$this->assertEquals(38, $data['id_28']);

		$result = $this->prepareResult(new ResultSet(4, 3));
		$data   = $result->getSortedRelevanceByExternalId();
		$this->assertCount(4, $data);
		$this->assertEquals(36, $data['id_26']);
		$this->assertEquals(35, $data['id_25']);
		$this->assertEquals(34, $data['id_24']);
		$this->assertEquals(33, $data['id_23']);
	}

	/**
	 * @expectedException \S2\Rose\Exception\UnknownIdException
	 */
	public function testSetRelevanceInvalidExternalId()
	{
		$result = $this->prepareResult(new ResultSet());
		$result->setRelevanceRatio('not_found', 2);
	}

	/**
	 * @expectedException \S2\Rose\Exception\RuntimeException
	 */
	public function testSetRelevanceInvalidRatio()
	{
		$result = $this->prepareResult(new ResultSet());
		$result->setRelevanceRatio('id_10', array('not a number'));
	}

	public function testSetRelevance()
	{
		$result = $this->prepareResult(new ResultSet(2));
		$foundExternalIds = $result->getFoundExternalIds();
		$this->assertCount(30, $foundExternalIds);
		$this->assertContains('id_29', $foundExternalIds);
		$this->assertContains('id_10', $foundExternalIds);

		$result->setRelevanceRatio('id_10', 2.7);

		$data = $result->getSortedRelevanceByExternalId();

		$this->assertCount(2, $data);
		$this->assertEquals((10 + 10) * 2.7, $data['id_10']);
		$this->assertEquals((10 + 29), $data['id_29']);
	}

	/**
	 * @expectedException \S2\Rose\Exception\ImmutableException
	 * @expectedExceptionMessage One cannot set relevance ratios after sorting the result.
	 */
	public function testNoSetRelevanceAfterSorting()
	{
		$result = $this->prepareResult(new ResultSet(2));
		$this->assertContains('id_10', $result->getFoundExternalIds());

		$result->setRelevanceRatio('id_10', 2.72);

		$data = $result->getSortedRelevanceByExternalId();

		$result->setRelevanceRatio('id_10', 3.14);
	}

	public function testEmpty()
	{
		$resultSet = new ResultSet();
		$resultSet->freeze();
		$data = $resultSet->getItems();
		$this->assertCount(0, $data);
	}

	/**
	 * @expectedException \S2\Rose\Exception\ImmutableException
	 */
	public function testNotFrozenGetItems()
	{
		$resultSet = new ResultSet();
		$resultSet->getItems();
	}

	/**
	 * @expectedException \S2\Rose\Exception\UnknownIdException
	 */
	public function testNotFrozenAttachSnippet()
	{
		$resultSet = new ResultSet();
		$resultSet->attachSnippet('not found', new Snippet('', '', 1));
	}

	/**
	 * @expectedException \S2\Rose\Exception\ImmutableException
	 */
	public function testNotFrozenGetFoundExternalIds()
	{
		$resultSet = new ResultSet();
		$resultSet->getFoundExternalIds();
	}

	/**
	 * @expectedException \S2\Rose\Exception\ImmutableException
	 */
	public function testNotFrozenSetRelevanceRatio()
	{
		$resultSet = new ResultSet();
		$resultSet->setRelevanceRatio('not found', 2);
	}

	/**
	 * @expectedException \S2\Rose\Exception\ImmutableException
	 */
	public function testNotFrozenGetFoundWordsByExternalId()
	{
		$resultSet = new ResultSet();
		$resultSet->getFoundWordsByExternalId();
	}

	/**
	 * @expectedException \S2\Rose\Exception\ImmutableException
	 */
	public function testNotFrozenGetSortedExternalIds()
	{
		$resultSet = new ResultSet();
		$resultSet->getSortedExternalIds();
	}

	/**
	 * @expectedException \S2\Rose\Exception\ImmutableException
	 */
	public function testNotFrozenGetSortedRelevanceByExternalId()
	{
		$resultSet = new ResultSet();
		$resultSet->getSortedRelevanceByExternalId();
	}

	/**
	 * @param ResultSet $result
	 *
	 * @return ResultSet
	 */
	private function prepareResult(ResultSet $result)
	{
		for ($i = 30; $i--;) {
			$externalId = 'id_' . $i;
			$result->addWordWeight('test1', $externalId, $i);
			$result->addWordWeight('test2', $externalId, 10);
		}

		$result->freeze();

		return $result;
	}
}
