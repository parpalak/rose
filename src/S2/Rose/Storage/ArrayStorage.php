<?php
/**
 * @copyright 2016-2018 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

use S2\Rose\Entity\TocEntry;
use S2\Rose\Exception\UnknownIdException;
use S2\Rose\Finder;

/**
 * Class ArrayStorage
 */
abstract class ArrayStorage implements StorageReadInterface, StorageWriteInterface
{
    /**
     * @var array
     */
    protected $excludedWords = [];

    /**
     * @var array
     */
    protected $indexSingleKeywords = [];

    /**
     * @var array
     */
    protected $indexBaseKeywords = [];

    /**
     * @var array
     */
    protected $indexMultiKeywords = [];

    /**
     * @var TocEntry[]
     */
    protected $toc = [];

    /**
     * @var FulltextProxyInterface
     */
    protected $fulltextProxy;

    /**
     * @var array
     */
    protected $externalIdMap = [];

    /**
     * {@inheritdoc}
     */
    public function fulltextResultByWords(array $words)
    {
        $result = new FulltextIndexContent();
        foreach ($words as $word) {
            $data = $this->makeKeysExternalIds($this->fulltextProxy->getByWord($word));
            foreach ($data as $externalId => $positions) {
                foreach ($positions as $position) {
                    $result->add($word, $externalId, $position);
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isExcluded($word)
    {
        return isset($this->excludedWords[$word]);
    }

    /**
     * {@inheritdoc}
     */
    public function addToFulltext(array $words, $externalId)
    {
        $id = $this->internalIdFromExternalId($externalId);
        foreach ($words as $position => $word) {
            $this->fulltextProxy->addWord($word, $id, (int)$position);
        }
    }

    /**
     * Drops frequent words from index.
     */
    public function cleanup()
    {
        $threshold = Finder::fulltextRateExcludeNum(count($this->toc));

        foreach ($this->fulltextProxy->getFrequentWords($threshold) as $word => $stat) {
            // Drop fulltext frequent or empty items
            $this->fulltextProxy->removeWord($word);
            $this->excludedWords[$word] = 1;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeFromIndex($externalId)
    {
        $internalId = $this->internalIdFromExternalId($externalId);

        $this->fulltextProxy->removeById($internalId);

        foreach ($this->indexSingleKeywords as &$data) {
            if (isset($data[$internalId])) {
                unset($data[$internalId]);
            }
        }
        unset($data);

        foreach ($this->indexBaseKeywords as &$data2) {
            if (isset($data2[$internalId])) {
                unset($data2[$internalId]);
            }
        }
        unset($data2);

        foreach ($this->indexMultiKeywords as &$data3) {
            if (isset($data3[$internalId])) {
                unset($data3[$internalId]);
            }
        }
        unset($data3);
    }

    /**
     * {@inheritdoc}
     */
    public function getSingleKeywordIndexByWords(array $words)
    {
        $result = [];
        foreach ($words as $word) {
            if (isset($this->indexSingleKeywords[$word])) {
                $result[$word] = $this->makeKeysExternalIds($this->indexSingleKeywords[$word]);
            } else {
                $result[$word] = [];
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function addToSingleKeywordIndex($word, $externalId, $type)
    {
        $this->indexSingleKeywords[$word][$this->internalIdFromExternalId($externalId)] = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultipleKeywordIndexByString($string)
    {
        $string = ' ' . $string . ' ';

        $result = [];
        foreach ($this->indexMultiKeywords as $keyword => $weightsById) {
            if (strpos($string, ' ' . $keyword . ' ') !== false) {
                $result[] = $this->makeKeysExternalIds($weightsById);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function addToMultipleKeywordIndex($string, $externalId, $type)
    {
        $this->indexMultiKeywords[$string][$this->internalIdFromExternalId($externalId)] = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getTocByExternalIds($externalIds)
    {
        $result = [];
        foreach ($externalIds as $externalId) {
            if (isset($this->toc[$externalId])) {
                $result[$externalId] = $this->toc[$externalId];
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getTocByExternalId($externalId)
    {
        return isset($this->toc[$externalId]) ? $this->toc[$externalId] : null;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function makeKeysExternalIds(array $data)
    {
        $result = [];
        foreach ($data as $id => $items) {
            $externalId          = $this->externalIdFromInternalId($id);
            $result[$externalId] = $items;
        }

        return $result;
    }

    /**
     * @param int $internalId
     *
     * @return string
     */
    private function externalIdFromInternalId($internalId)
    {
        return isset($this->externalIdMap[$internalId]) ? $this->externalIdMap[$internalId] : null;
    }

    /**
     * @param string $externalId
     *
     * @return int
     */
    private function internalIdFromExternalId($externalId)
    {
        if (!isset($this->toc[$externalId])) {
            throw UnknownIdException::createIndexMissingExternalId($externalId);
        }

        return $this->toc[$externalId]->getInternalId();
    }

    /**
     * {@inheritdoc}
     */
    public function addItemToToc(TocEntry $entry, $externalId)
    {
        try {
            $internalId = $this->internalIdFromExternalId($externalId);
            $this->removeFromToc($externalId);
        } catch (UnknownIdException $e) {
            $internalId = 0;
            foreach ($this->toc as $existingEntry) {
                $internalId = max($internalId, $existingEntry->getInternalId());
            }
            $internalId++;
        }

        $entry->setInternalId($internalId);

        $this->toc[$externalId]           = $entry;
        $this->externalIdMap[$internalId] = $externalId;
    }

    /**
     * {@inheritdoc}
     */
    public function removeFromToc($externalId)
    {
        if (!isset($this->toc[$externalId])) {
            return;
        }

        $internalId = $this->toc[$externalId]->getInternalId();
        unset($this->externalIdMap[$internalId], $this->toc[$externalId]);
    }

    /**
     * {@inheritdoc}
     */
    public function findTocByTitle($title)
    {
        $result = [];
        foreach ($this->toc as $externalId => $entry) {
            if (mb_stripos($entry->getTitle(), $title) !== false) {
                $result[$externalId] = $entry;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getTocSize()
    {
        return count($this->toc);
    }
}
