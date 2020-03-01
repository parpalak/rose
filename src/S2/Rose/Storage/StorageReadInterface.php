<?php
/**
 * @copyright 2016-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

use S2\Rose\Entity\ExternalIdCollection;
use S2\Rose\Entity\TocEntryWithExternalId;

interface StorageReadInterface
{
    /**
     * @param string[] $words
     * @param null     $instanceId
     *
     * @return FulltextIndexContent
     */
    public function fulltextResultByWords(array $words, $instanceId = null);

    /**
     * @param string $word
     *
     * @return bool
     */
    public function isExcluded($word);

    /**
     * @param string[] $words
     * @param int|null $instanceId
     *
     * @return array|KeywordIndexContent[]
     */
    public function getSingleKeywordIndexByWords(array $words, $instanceId = null);

    /**
     * @param string   $string
     * @param int|null $instanceId
     *
     * @return KeywordIndexContent
     */
    public function getMultipleKeywordIndexByString($string, $instanceId = null);

    /**
     * @param ExternalIdCollection $externalIds
     *
     * @return TocEntryWithExternalId[]
     */
    public function getTocByExternalIds(ExternalIdCollection $externalIds);

    /**
     * @param int|null $instanceId
     *
     * @return int
     */
    public function getTocSize($instanceId);
}
