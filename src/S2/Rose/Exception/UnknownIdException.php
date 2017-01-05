<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Exception;

/**
 * Class UnknownIdExtension
 */
class UnknownIdException extends RuntimeException
{
	/**
	 * @param string $externalId
	 *
	 * @return static
	 */
	public static function createIndexMissingExternalId($externalId)
	{
		return new static(sprintf('External id "%s" not found in index.', $externalId));
	}

	/**
	 * @param string $externalId
	 *
	 * @return static
	 */
	public static function createResultMissingExternalId($externalId)
	{
		return new static(sprintf('External id "%s" not found in result.', $externalId));
	}
}
