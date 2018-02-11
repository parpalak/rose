<?php
/**
 * @copyright 2016-2018 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test;

use Codeception\Test\Unit;
use Codeception\Util\Stub;
use S2\Rose\Entity\Query;
use S2\Rose\Entity\TocEntry;
use S2\Rose\Finder;
use S2\Rose\Stemmer\PorterStemmerRussian;
use S2\Rose\Storage\FulltextIndexContent;
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
		$storage = Stub::makeEmpty(StorageReadInterface::class, array(
			'getMultipleKeywordIndexByString' => function ($word) {
				return [];
			},
			'getTocSize' => function () {
				return 30;
			},
			'fulltextResultByWords' => function (array $words) {
				$result = new FulltextIndexContent();
				foreach ($words as $k => $word) {
					if ($word == 'find') {
						$result->add($word, 'id_3', 1);
						$result->add($word, 'id_2', 10);
						$result->add($word, 'id_2', 20);
					}
					if ($word == 'and') {
						$result->add($word, 'id_1', 4);
						$result->add($word, 'id_1', 8);

						$result->add($word, 'id_2', 7);
						$result->add($word, 'id_2', 11);
						$result->add($word, 'id_2', 34);

						$result->add($word, 'id_3', 28);
						$result->add($word, 'id_3', 65);

						$result->add($word, 'id_4', 45);
						$result->add($word, 'id_4', 9);

						$result->add($word, 'id_5', 1);
						$result->add($word, 'id_6', 1);
						$result->add($word, 'id_7', 1);
						$result->add($word, 'id_8', 1);
						$result->add($word, 'id_9', 1);
						$result->add($word, 'id_10', 1);
						$result->add($word, 'id_11', 1);
						$result->add($word, 'id_12', 1);
						$result->add($word, 'id_13', 1);
						$result->add($word, 'id_14', 1);
						$result->add($word, 'id_15', 1);
						$result->add($word, 'id_16', 1);
						$result->add($word, 'id_17', 1);
						$result->add($word, 'id_18', 1);
						$result->add($word, 'id_19', 1);
						$result->add($word, 'id_20', 1);
						$result->add($word, 'id_21', 1);
					}
					if ($word == 'replace') {
						$result->add($word, 'id_2', 12);
					}

					unset($words[$k]);
				}

				if (!empty($words)) {
					throw new \RuntimeException(sprintf('Unknown words "%s" in StorageReadInterface stub.', implode(',', $words)));
				}

				return $result;
			},
			'getSingleKeywordIndexByWords' => function ($words) {
				$result = [];
				foreach ($words as $word) {
					if ($word == 'find') {
						$result[$word] = ['id_1' => 1, 'id_2' => 2];
					}
					elseif ($word == 'and') {
						$result[$word] = [];
					}
					elseif ($word == 'replace') {
						$result[$word] = ['id_1' => 1];
					}
					else {
						throw new \RuntimeException(sprintf('Unknown word "%s" in StorageReadInterface stub.', $word));
					}
				}
				return $result;
			},
			'getTocByExternalIds' => function (array $ids) {
				return array_map(function ($id) {
					return new TocEntry('Title ' . $id, '', null, 'url_' . $id, 'hash_' . $id);
				}, array_combine($ids, $ids));
			}
		));

		$stemmer = new PorterStemmerRussian();
		$finder  = new Finder($storage, $stemmer);
		$resultSet = $finder->find(new Query('find and replace'));

		$items = $resultSet->getItems();
		$this->assertCount(21, $items);

		$weights = $resultSet->getFoundWordPositionsByExternalId();
		$this->assertCount(21, $weights);
		$this->assertEquals([], $weights['id_1']['find']);
		$this->assertEquals([], $weights['id_1']['replace']);
		$this->assertEquals([10, 20], $weights['id_2']['find']);
		$this->assertEquals([12], $weights['id_2']['replace']);
		$this->assertEquals([1], $weights['id_3']['find']);
	}
}
