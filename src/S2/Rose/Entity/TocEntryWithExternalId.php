<?php
/**
 * @copyright 2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

class TocEntryWithExternalId
{
    private $tocEntry;
    private $externalId;

    public function __construct(TocEntry $tocEntry, ExternalId $externalId)
    {
        $this->tocEntry   = $tocEntry;
        $this->externalId = $externalId;
    }

    /**
     * @return TocEntry
     */
    public function getTocEntry()
    {
        return $this->tocEntry;
    }

    /**
     * @return ExternalId
     */
    public function getExternalId()
    {
        return $this->externalId;
    }
}
