<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Search\Test\Entity;

use Codeception\Test\Unit;
use S2\Search\Entity\ResultSet;

/**
 * Class ResultTest
 *
 * @group entity
 */
class ResultSetTest extends Unit
{
	public function testLimit()
	{
		$result = $this->prepareResult(new ResultSet());
		$data   = $result->getWeightByExternalId();
		$this->assertCount(30, $data);


		$result = $this->prepareResult(new ResultSet(2));
		$data   = $result->getWeightByExternalId();
		$this->assertCount(2, $data);
		$this->assertEquals(39, $data['id_29']);
		$this->assertEquals(38, $data['id_28']);

		$result = $this->prepareResult(new ResultSet(4, 3));
		$data   = $result->getWeightByExternalId();
		$this->assertCount(4, $data);
		$this->assertEquals(36, $data['id_26']);
		$this->assertEquals(35, $data['id_25']);
		$this->assertEquals(34, $data['id_24']);
		$this->assertEquals(33, $data['id_23']);
	}

	public function testEmpty()
	{
		$resultSet = new ResultSet();
		$resultSet->freeze();
		$data = $resultSet->getItems();
		$this->assertCount(0, $data);
	}


	/**
	 * @param ResultSet $result
	 *
	 * @return ResultSet
	 */
	private function prepareResult(ResultSet $result)
	{
		for ($i = 30; $i--;) {
			$result->addWordWeight('test1', 'id_' . $i, $i);
			$result->addWordWeight('test2', 'id_' . $i, 10);
		}

		$result->freeze();

		return $result;
	}
}
