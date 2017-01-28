<?php
/**
 * @copyright 2017 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

/**
 * Interface CacheableStorageInterface
 */
interface CacheableStorageInterface
{
	/**
	 * Clears the internal cache of stored items
	 */
	public function clearTocCache();
}
