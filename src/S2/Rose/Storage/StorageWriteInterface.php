<?php
/**
 * @copyright 2016-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\TocEntry;

interface StorageWriteInterface
{
    /**
     * @param array      $words Keys are the positions of corresponding words.
     * @param ExternalId $externalId
     */
    public function addToFulltext(array $words, ExternalId $externalId);

    /**
     * @param ExternalId $externalId
     */
    public function removeFromIndex(ExternalId $externalId);

    /**
     * @param string $word
     *
     * @return bool
     */
    public function isExcluded($word);

    /**
     * @param string     $word
     * @param ExternalId $externalId
     * @param string     $type
     */
    public function addToSingleKeywordIndex($word, ExternalId $externalId, $type);

    /**
     * @param string     $string
     * @param ExternalId $externalId
     * @param string     $type
     */
    public function addToMultipleKeywordIndex($string, ExternalId $externalId, $type);

    /**
     * @param TocEntry   $entry
     * @param ExternalId $externalId
     */
    public function addEntryToToc(TocEntry $entry, ExternalId $externalId);

    /**
     * TODO How can a read method be eliminated from the writer interface?
     *
     * @param ExternalId $externalId
     *
     * @return TocEntry
     */
    public function getTocByExternalId(ExternalId $externalId);

    /**
     * @param ExternalId $externalId
     */
    public function removeFromToc(ExternalId $externalId);
}
