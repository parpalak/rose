<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Search\Storage;

use S2\Search\Entity\TocEntry;

/**
 * Interface IndexWriteInterface
 */
interface StorageWriteInterface
{
	/**
	 * @param string $word
	 * @param string $externalId
	 * @param int    $position
	 */
	public function addToFulltext($word, $externalId, $position);

	/**
	 * @param $externalId
	 */
	public function removeFromIndex($externalId);

	/**
	 * @param $word
	 *
	 * @return bool
	 */
	public function isExcluded($word);

	/**
	 * @param string $word
	 * @param string $externalId
	 * @param string $type
	 */
	public function addToSingleKeywordIndex($word, $externalId, $type);

	/**
	 * @param string $string
	 * @param string $externalId
	 * @param string $type
	 */
	public function addToMultipleKeywordIndex($string, $externalId, $type);

	/**
	 * @param TocEntry $entry
	 * @param string   $externalId
	 */
	public function addItemToToc(TocEntry $entry, $externalId);

	/**
	 * TODO How can a read method be eliminated from the writer interface?
	 *
	 * @param string $externalId
	 *
	 * @return TocEntry
	 */
	public function getTocByExternalId($externalId);

	/**
	 * @param $externalId
	 */
	public function removeFromToc($externalId);
}
