<?php
/**
 * @copyright 2016-2018 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

use S2\Rose\Entity\TocEntry;

/**
 * Interface IndexReadInterface
 */
interface StorageReadInterface
{
    /**
     * @param string[] $words
     *
     * @return FulltextIndexContent
     */
    public function fulltextResultByWords(array $words);

    /**
     * @param string $word
     *
     * @return bool
     */
    public function isExcluded($word);

    /**
     * @param string[] $words
     *
     * @return array
     */
    public function getSingleKeywordIndexByWords(array $words);

    /**
     * @param string $string
     *
     * @return array
     */
    public function getMultipleKeywordIndexByString($string);

    /**
     * @param string[] $externalIds
     *
     * @return array|TocEntry[]
     */
    public function getTocByExternalIds($externalIds);

    /**
     * @return int
     */
    public function getTocSize();

    /**
     * @param string $title
     *
     * @return TocEntry[]
     */
    public function findTocByTitle($title);
}
