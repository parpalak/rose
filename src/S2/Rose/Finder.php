<?php
/**
 * Fulltext and keyword search
 *
 * @copyright 2010-2017 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose;

use S2\Rose\Entity\Query;
use S2\Rose\Entity\ResultSet;
use S2\Rose\Exception\UnknownKeywordTypeException;
use S2\Rose\Stemmer\StemmerInterface;
use S2\Rose\Storage\StorageReadInterface;

/**
 * Class Finder
 */
class Finder
{
	const TYPE_TITLE   = 1;
	const TYPE_KEYWORD = 2;

	/**
	 * @var StorageReadInterface
	 */
	protected $storage;

	/**
	 * @var StemmerInterface
	 */
	protected $stemmer;

	/**
	 * Finder constructor.
	 *
	 * @param StorageReadInterface $storage
	 * @param StemmerInterface     $stemmer
	 */
	public function __construct(StorageReadInterface $storage, StemmerInterface $stemmer)
	{
		$this->storage = $storage;
		$this->stemmer = $stemmer;
	}

	/**
	 * @param number[] $a1
	 * @param number[] $a2
	 *
	 * @return number
	 */
	protected static function compareArrays(array $a1, array $a2)
	{
		$result = 100000000;
		foreach ($a1 as $x) {
			foreach ($a2 as $y) {
				if (abs($x - $y) < $result) {
					$result = abs($x - $y);
				}
			}
		}

		return $result;
	}

	/**
	 * @param int $type
	 *
	 * @return int
	 */
	protected static function getKeywordWeight($type)
	{
		if ($type == self::TYPE_KEYWORD) {
			return 30;
		}

		if ($type == self::TYPE_TITLE) {
			return 20;
		}

		throw new UnknownKeywordTypeException(sprintf('Unknown type "%s"', $type));
	}

	/**
	 * Weight for a found fulltext word depending on a number of words in the search query.
	 *
	 * @param $wordNum
	 *
	 * @return float
	 */
	protected static function fulltextWordWeight($wordNum)
	{
		if ($wordNum < 4) {
			return 1;
		}

		if ($wordNum == 4) {
			return 7;
		}

		return 10;
	}

	/**
	 * Ignore frequent words encountering in indexed items.
	 *
	 * @param $tocSize
	 *
	 * @return mixed
	 */
	public static function fulltextRateExcludeNum($tocSize)
	{
		return max($tocSize * 0.5, 20);
	}

	/**
	 * Additional weight for close words in an indexed item.
	 *
	 * @param int $wordDistance
	 *
	 * @return float
	 */
	protected static function neighbourWeight($wordDistance)
	{
		return max(23 - $wordDistance, 13);
	}

	/**
	 * Weight ratio for repeating words in an indexed item.
	 *
	 * @param int $repeatNum
	 *
	 * @return float
	 */
	protected static function repeatWeightRatio($repeatNum)
	{
		return min(0.5 * ($repeatNum - 1) + 1, 4);
	}

	/**
	 * @param string[]  $words
	 * @param ResultSet $result
	 */
	protected function findFulltext(array $words, ResultSet $result)
	{
		$wordWeight    = self::fulltextWordWeight(count($words));
		$prevPositions = array();

		foreach ($words as $word) {
			$currPositions = array();
			foreach (array_unique(array($word, $this->stemmer->stemWord($word))) as $searchWord) {
				$fulltextIndexByWord = $this->storage->getFulltextByWord($searchWord);
				if (count($fulltextIndexByWord) > self::fulltextRateExcludeNum($this->storage->getTocSize())) {
					continue;
				}
				$currPositions = array_merge($currPositions, $fulltextIndexByWord);

				foreach ($fulltextIndexByWord as $externalId => $positions) {
					$curWeight = $wordWeight * self::repeatWeightRatio(count($positions));
					$result->addWordWeight($word, $externalId, $curWeight, $positions);
				}
			}

			foreach ($currPositions as $externalId => $positions) {
				if (isset($prevPositions[$externalId])) {
					$minWordDistance = self::compareArrays($positions, $prevPositions[$externalId]);
					$weight          = self::neighbourWeight($minWordDistance) * $wordWeight;
					$result->addNeighbourWeight($word, $externalId, $weight);
				}
			}

			$prevPositions = $currPositions;
		}
	}

	/**
	 * @param string    $word
	 * @param ResultSet $result
	 */
	protected function findSimpleKeywords($word, ResultSet $result)
	{
		foreach ($this->storage->getSingleKeywordIndexByWord($word) as $externalId => $type) {
			$result->addWordWeight($word, $externalId, self::getKeywordWeight($type));
		}
	}

	/**
	 * @param string    $string
	 * @param ResultSet $result
	 */
	protected function findSpacedKeywords($string, ResultSet $result)
	{
		foreach ($this->storage->getMultipleKeywordIndexByString($string) as $externalId => $type) {
			$result->addWordWeight($string, $externalId, self::getKeywordWeight($type));
		}
	}

	/**
	 * @param Query $query
	 * @param bool  $isDebug
	 *
	 * @return ResultSet
	 */
	public function find(Query $query, $isDebug = false)
	{
		$result = new ResultSet($query->getLimit(), $query->getOffset(), $isDebug);

		$rawWords     = $query->valueToArray();
		$cleanedQuery = implode(' ', $rawWords);
		$result->addProfilePoint('Input cleanup');

		if (count($rawWords) > 1) {
			$this->findSpacedKeywords($cleanedQuery, $result);
		}
		$result->addProfilePoint('Keywords with space');

		foreach ($rawWords as $word) {
			$this->findSimpleKeywords($word, $result);
		}
		$result->addProfilePoint('Simple keywords');

		$this->findFulltext($rawWords, $result);
		$result->addProfilePoint('Fulltext search');

		$result->freeze();

		foreach ($result->getFoundExternalIds() as $externalId) {
			$result->attachToc($externalId, $this->storage->getTocByExternalId($externalId));
		}

		return $result;
	}
}

