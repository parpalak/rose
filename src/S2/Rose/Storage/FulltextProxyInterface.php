<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

/**
 * Interface FulltextProxyInterface
 */
interface FulltextProxyInterface
{
	/**
	 * @param string $word
	 *
	 * @return array[]
	 */
	public function getByWord($word);

	/**
	 * @param string $word
	 * @param int    $id
	 * @param int    $position
	 */
	public function addWord($word, $id, $position);

	/**
	 * @param string $word
	 */
	public function removeWord($word);

	/**
	 * @param int $threshold
	 *
	 * @return mixed
	 */
	public function getFrequentWords($threshold);

	/**
	 * @param int $id
	 */
	public function removeById($id);
}
