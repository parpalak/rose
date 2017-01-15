<?php
/**
 * @copyright 2016-2017 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test;

use Codeception\Test\Unit;
use Codeception\Util\Stub;
use S2\Rose\Entity\Query;
use S2\Rose\Entity\TocEntry;
use S2\Rose\Finder;
use S2\Rose\Stemmer\PorterStemmerRussian;
use S2\Rose\Storage\StorageReadInterface;

/**
 * Class FinderTest
 *
 * @group finder
 */
class FinderTest extends Unit
{
	public function testIgnoreFrequentWordsInFulltext()
	{
		$storage = Stub::makeEmpty(StorageReadInterface::class, [
			'getMultipleKeywordIndexByString' => function ($word) {
				return [];
			},
			'getTocSize' => function () {
				return 30;
			},
			'getFulltextByWord' => function ($word) {
				if ($word == 'find') {
					return ['id_3' => [1], 'id_2' => [10, 20]];
				}
				if ($word == 'and') {
					return [
						'id_1' => [4, 8],
						'id_2' => [7, 11, 34],
						'id_3' => [28, 65],
						'id_4' => [45, 9],
						'id_5' => [1],
						'id_6' => [1],
						'id_7' => [1],
						'id_8' => [1],
						'id_9' => [1],
						'id_10' => [1],
						'id_11' => [1],
						'id_12' => [1],
						'id_13' => [1],
						'id_14' => [1],
						'id_15' => [1],
						'id_16' => [1],
						'id_17' => [1],
						'id_18' => [1],
						'id_19' => [1],
						'id_20' => [1],
						'id_21' => [1],
					];
				}
				if ($word == 'replace') {
					return ['id_2' => [12]];
				}
				throw new \RuntimeException(sprintf('Unknown word "%s" in StorageReadInterface stub.', $word));
			},
			'getSingleKeywordIndexByWord' => function ($word) {
				if ($word == 'find') {
					return ['id_1' => 1, 'id_2' => 2];
				}
				if ($word == 'and') {
					return [];
				}
				if ($word == 'replace') {
					return ['id_1' => 1];
				}
				throw new \RuntimeException(sprintf('Unknown word "%s" in StorageReadInterface stub.', $word));
			},
			'getTocByExternalId' => function ($id) {
				return new TocEntry('Title ' . $id, '', null, 'url_' . $id, 'hash_' . $id);
			}
		]);

		$stemmer = new PorterStemmerRussian();
		$finder  = new Finder($storage, $stemmer);
		$resultSet = $finder->find(new Query('find and replace'));

		$items = $resultSet->getItems();
		$this->assertCount(3, $items);

		$weights = $resultSet->getFoundWordPositionsByExternalId();
		$this->assertCount(3, $weights);
		$this->assertEquals(['find' => [], 'replace' => []], $weights['id_1']);
		$this->assertEquals(['find' => [10, 20], 'replace' => [12]], $weights['id_2']);
		$this->assertEquals(['find' => [1]], $weights['id_3']);
	}
}
