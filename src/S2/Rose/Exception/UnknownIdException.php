<?php
/**
 * @copyright 2016-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Exception;

use S2\Rose\Entity\ExternalId;

class UnknownIdException extends RuntimeException
{
    public static function createIndexMissingExternalId(ExternalId $externalId)
    {
        return new static(sprintf(
            'External id "%s" for instance "%s" not found in index.',
            $externalId->getId(),
            $externalId->getInstanceId()
        ));
    }

    public static function createResultMissingExternalId(ExternalId $externalId)
    {
        return new static(sprintf(
            'External id "%s" for instance "%s" not found in result.',
            $externalId->getId(),
            $externalId->getInstanceId()
        ));
    }
}
