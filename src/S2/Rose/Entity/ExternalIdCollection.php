<?php
/**
 * @copyright 2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

use S2\Rose\Exception\InvalidArgumentException;

class ExternalIdCollection
{
    /**
     * @var ExternalId[]
     */
    private $externalIds;

    /**
     * @param ExternalId[] $externalIds
     */
    public function __construct(array $externalIds)
    {
        foreach ($externalIds as $externalId) {
            if (!$externalId instanceof ExternalId) {
                throw new InvalidArgumentException('External ids must be an array of ExternalId.');
            }
        }

        $this->externalIds = $externalIds;
    }

    /**
     * @param string[] $serializedExternalIds
     *
     * @return ExternalIdCollection
     */
    public static function fromStringArray(array $serializedExternalIds)
    {
        return new self(array_map(static function ($serializedExtId) {
            return ExternalId::fromString($serializedExtId);
        }, $serializedExternalIds));
    }

    /**
     * @return ExternalId[]
     */
    public function toArray()
    {
        return $this->externalIds;
    }
}
