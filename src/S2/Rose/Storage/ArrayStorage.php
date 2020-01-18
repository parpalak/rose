<?php
/**
 * @copyright 2016-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\ExternalIdCollection;
use S2\Rose\Entity\TocEntry;
use S2\Rose\Entity\TocEntryWithExternalId;
use S2\Rose\Exception\UnknownIdException;
use S2\Rose\Finder;

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
     * @var array|ExternalId
     */
    protected $externalIdMap = [];

    /**
     * {@inheritdoc}
     */
    public function fulltextResultByWords(array $words, $instanceId = null)
    {
        $result = new FulltextIndexContent();
        foreach ($words as $word) {
            $data = $this->fulltextProxy->getByWord($word);
            foreach ($data as $id => $positions) {
                $externalId = $this->externalIdFromInternalId($id);
                if ($instanceId === null || $externalId->getInstanceId() === $instanceId) {
                    foreach ($positions as $position) {
                        $result->add($word, $externalId, $position);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getSingleKeywordIndexByWords(array $words, $instanceId = null)
    {
        $result = [];
        foreach ($words as $word) {
            $result[$word] = new KeywordIndexContent();
            if (isset($this->indexSingleKeywords[$word])) {
                foreach ($this->indexSingleKeywords[$word] as $id => $type) {
                    $externalId = $this->externalIdFromInternalId($id);
                    if ($instanceId === null || $externalId->getInstanceId() === $instanceId) {
                        $result[$word]->add($externalId, $type);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultipleKeywordIndexByString($string, $instanceId = null)
    {
        $string = ' ' . $string . ' ';

        $result = new KeywordIndexContent();
        foreach ($this->indexMultiKeywords as $keyword => $typesById) {
            if (strpos($string, ' ' . $keyword . ' ') !== false) {
                foreach ($typesById as $id => $type) {
                    $externalId = $this->externalIdFromInternalId($id);
                    if ($instanceId === null || $externalId->getInstanceId() === $instanceId) {
                        $result->add($externalId, $type);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     * @throws UnknownIdException
     */
    public function addToFulltext(array $words, ExternalId $externalId)
    {
        $id = $this->internalIdFromExternalId($externalId);
        foreach ($words as $position => $word) {
            $this->fulltextProxy->addWord($word, $id, (int)$position);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isExcluded($word)
    {
        return isset($this->excludedWords[$word]);
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
     * @throws UnknownIdException
     */
    public function addToSingleKeywordIndex($word, ExternalId $externalId, $type)
    {
        $this->indexSingleKeywords[$word][$this->internalIdFromExternalId($externalId)] = $type;
    }

    /**
     * {@inheritdoc}
     * @throws UnknownIdException
     */
    public function addToMultipleKeywordIndex($string, ExternalId $externalId, $type)
    {
        $this->indexMultiKeywords[$string][$this->internalIdFromExternalId($externalId)] = $type;
    }

    /**
     * {@inheritdoc}
     * @throws UnknownIdException
     */
    public function removeFromIndex(ExternalId $externalId)
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
    public function addEntryToToc(TocEntry $entry, ExternalId $externalId)
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

        $this->toc[$externalId->toString()] = $entry;
        $this->externalIdMap[$internalId]   = $externalId;
    }

    /**
     * {@inheritdoc}
     */
    public function getTocByExternalIds(ExternalIdCollection $externalIds, $instanceId = null)
    {
        $result = [];
        foreach ($externalIds->toArray() as $externalId) {
            $serializedExtId = $externalId->toString();
            if (isset($this->toc[$serializedExtId])) {
                $result[] = new TocEntryWithExternalId($this->toc[$serializedExtId], $externalId);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getTocByExternalId(ExternalId $externalId)
    {
        $serializedExtId = $externalId->toString();

        return isset($this->toc[$serializedExtId]) ? $this->toc[$serializedExtId] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function removeFromToc(ExternalId $externalId)
    {
        $serializedExtId = $externalId->toString();
        if (!isset($this->toc[$serializedExtId])) {
            return;
        }

        $internalId = $this->toc[$serializedExtId]->getInternalId();
        unset($this->externalIdMap[$internalId], $this->toc[$serializedExtId]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTocSize($instanceId)
    {
        return count($this->toc);
    }

    /**
     * @param ExternalId $externalId
     *
     * @return int
     * @throws UnknownIdException
     */
    private function internalIdFromExternalId(ExternalId $externalId)
    {
        $serializedExtId = $externalId->toString();
        if (!isset($this->toc[$serializedExtId])) {
            throw UnknownIdException::createIndexMissingExternalId($externalId);
        }

        return $this->toc[$serializedExtId]->getInternalId();
    }

    /**
     * @param int $internalId
     *
     * @return ExternalId
     */
    private function externalIdFromInternalId($internalId)
    {
        return isset($this->externalIdMap[$internalId]) ? $this->externalIdMap[$internalId] : null;
    }
}
