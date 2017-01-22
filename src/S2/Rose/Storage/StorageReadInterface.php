<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

use S2\Rose\Entity\TocEntry;

/**
 * Interface IndexReadInterface
 */
interface StorageReadInterface
{
	/**
	 * @param $word
	 *
	 * @return mixed
	 */
	public function getFulltextByWord($word);

	/**
	 * @param $word
	 *
	 * @return bool
	 */
	public function isExcluded($word);

	/**
	 * @param string[] $words
	 *
	 * @return array
	 */
	public function getSingleKeywordIndexByWords(array $words);

	/**
	 * @param string $string
	 *
	 * @return array
	 */
	public function getMultipleKeywordIndexByString($string);

	/**
	 * @param string $externalId
	 *
	 * @return TocEntry
	 */
	public function getTocByExternalId($externalId);

	/**
	 * @return int
	 */
	public function getTocSize();

	/**
	 * @param $string
	 *
	 * @return TocEntry[]
	 */
	public function findTocByTitle($string);
}
