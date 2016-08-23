<?php
/**
 * Fulltext and keyword search
 *
 * @copyright 2010-2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Search;

use S2\Search\Entity\Result;
use S2\Search\Exception\UnknownKeywordTypeException;
use S2\Search\Stemmer\StemmerInterface;
use S2\Search\Storage\StorageReadInterface;

/**
 * Class Finder
 */
class Finder
{
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
	 * @param string $content
	 *
	 * @return string[]
	 */
	public static function filterInput($content)
	{
		$content = strip_tags($content);

		$content = str_replace(['«', '»', '“', '”', '‘', '’'], '"', $content);
		$content = str_replace(['---', '--', '–', '−',], '—', $content);
		$content = preg_replace('#,\s+,#u', ',,', $content);
		$content = preg_replace('#[^\-а-яё0-9a-z\^\.,\(\)";?!…:—]+#iu', ' ', $content);
		$content = preg_replace('#\n+#', ' ', $content);
		$content = preg_replace('#\s+#u', ' ', $content);
		$content = mb_strtolower($content);

		$content = preg_replace('#(,+)#u', '\\1 ', $content);

		$content = preg_replace('#[ |\\/]+#', ' ', $content);

		$words = explode(' ', $content);
		foreach ($words as $k => $v) {
			// Separate special chars from the letter combination
			if (strlen($v) > 1) {
				foreach (['—', '^', '(', ')', '"', ':', '?', '!'] as $specialChar) {
					if (mb_substr($v, 0, 1) == $specialChar || mb_substr($v, -1) == $specialChar) {
						$words[$k] = str_replace($specialChar, '', $v);
						$words[]   = $specialChar;
					}
				}
			}

			// Separate hyphen from the letter combination
			if (strlen($v) > 1 && (substr($v, 0, 1) == '-' || substr($v, -1) == '-')) {
				$words[$k] = str_replace('-', '', $v);
				$words[]   = '-';
			}

			// Replace 'ё' inside words
			if (false !== strpos($v, 'ё') && $v != 'ё') {
				$words[$k] = str_replace('ё', 'е', $v);
			}

			// Remove ','
			if (preg_match('#^[^,]+,$#u', $v) || preg_match('#^,[^,]+$#u', $v)) {
				$words[$k] = str_replace(',', '', $v);
				$words[]   = ',';
			}
		}

		$words = array_filter($words, 'strlen');

		// Fix keys
		$words = array_values($words);

		return $words;
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
		if ($type === Indexer::TYPE_KEYWORD) {
			return 30;
		}

		if ($type === Indexer::TYPE_TITLE) {
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
	 * @param string[] $words
	 * @param Result   $result
	 */
	protected function findFulltext(array $words, Result $result)
	{
		$wordWeight    = self::fulltextWordWeight(count($words));
		$prevPositions = [];

		foreach ($words as $word) {
			$currPositions = [];
			foreach (array_unique([$word, $this->stemmer->stemWord($word)]) as $searchWord) {
				$fulltextIndexByWord = $this->storage->getFulltextByWord($searchWord);
				$currPositions       = array_merge($currPositions, $fulltextIndexByWord);

				foreach ($fulltextIndexByWord as $externalId => $entries) {
					$curWeight = $wordWeight * self::repeatWeightRatio(count($entries));
					$result->addWordWeight($word, $externalId, $curWeight);
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
	 * @param string $word
	 * @param Result $result
	 */
	protected function findSimpleKeywords($word, Result $result)
	{
		foreach ($this->storage->getSingleKeywordIndexByWord($word) as $externalId => $type) {
			$result->addWordWeight($word, $externalId, self::getKeywordWeight($type));
		}
	}

	/**
	 * @param string $string
	 * @param Result $result
	 */
	protected function findSpacedKeywords($string, Result $result)
	{
		foreach ($this->storage->getMultipleKeywordIndexByString($string) as $externalId => $type) {
			$result->addWordWeight($string, $externalId, self::getKeywordWeight($type));
		}
	}

	/**
	 * @param string $query
	 * @param bool   $isDebug
	 *
	 * @return Result
	 */
	public function find($query, $isDebug = false)
	{
		$result = new Result($isDebug);

		$rawWords     = self::filterInput($query);
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

		return $result;
	}
}

