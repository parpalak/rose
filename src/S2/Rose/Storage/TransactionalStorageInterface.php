<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

/**
 * Interface TransactionalStorageInterface
 */
interface TransactionalStorageInterface
{
	/**
	 * Starts a transaction
	 */
	public function startTransaction();

	/**
	 * Commits a transaction
	 */
	public function commitTransaction();

	/**
	 * Rollbacks a transaction
	 */
	public function rollbackTransaction();
}
