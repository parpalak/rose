<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Search\Storage;

use S2\Search\Entity\TocEntry;

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
	 * @param string $word
	 *
	 * @return array
	 */
	public function getSingleKeywordIndexByWord($word);

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
	 * @return TocEntry[]
	 */
	public function findTocByTitle($string);
}
