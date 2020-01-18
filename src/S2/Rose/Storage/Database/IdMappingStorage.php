<?php
/**
 * @copyright 2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage\Database;

use S2\Rose\Entity\ExternalId;

class IdMappingStorage
{
    protected $idMapping = [];

    /**
     * @param ExternalId $externalId
     * @param int        $internalId
     */
    public function add(ExternalId $externalId, $internalId)
    {
        $this->idMapping[$externalId->toString()] = $internalId;
    }

    public function remove(ExternalId $externalId)
    {
        unset($this->idMapping[$externalId->toString()]);
    }

    public function clear()
    {
        $this->idMapping = [];
    }

    public function get(ExternalId $externalId)
    {
        $externalIdString = $externalId->toString();
        if (!isset($this->idMapping[$externalIdString])) {
            return null;
        }

        return $this->idMapping[$externalIdString];
    }
}
