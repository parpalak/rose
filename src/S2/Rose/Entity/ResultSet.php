<?php
/**
 * @copyright 2016-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

use S2\Rose\Exception\ImmutableException;
use S2\Rose\Exception\InvalidArgumentException;
use S2\Rose\Exception\UnknownIdException;
use S2\Rose\Helper\ProfileHelper;

class ResultSet
{
    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var bool
     */
    protected $isDebug;

    /**
     * @var int
     */
    protected $startedAt;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $profilePoints = [];

    /**
     * @var bool
     */
    protected $isFrozen = false;

    /**
     * @var ResultItem[]
     */
    protected $items = [];

    /**
     * Result cache
     *
     * @var array
     */
    protected $sortedRelevance;

    /**
     * Relevance corrections
     *
     * @var array
     */
    protected $externalRelevanceRatios = [];

    /**
     * Positions of found words
     *
     * @var array
     */
    protected $positions = [];

    /**
     * @var string
     */
    protected $highlightTemplate = '<i>%s</i>';

    /**
     * @var ResultTrace
     */
    protected $trace;

    /**
     * @param int  $limit
     * @param int  $offset
     * @param bool $isDebug
     */
    public function __construct($limit = null, $offset = 0, $isDebug = false)
    {
        $this->limit   = $limit;
        $this->offset  = $offset;
        $this->isDebug = $isDebug;
        if ($isDebug) {
            $this->startedAt = microtime(true);
        }
        $this->trace = new ResultTrace();
    }

    /**
     * @param string $message
     */
    public function addProfilePoint($message)
    {
        if (!$this->isDebug) {
            return;
        }

        $this->profilePoints[] = ProfileHelper::getProfilePoint($message, -$this->startedAt + ($this->startedAt = microtime(1)));
    }

    /**
     * @return array
     */
    public function getProfilePoints()
    {
        $this->isFrozen = true;

        return $this->profilePoints;
    }

    /**
     * @param string $highlightTemplate
     *
     * @return $this
     */
    public function setHighlightTemplate($highlightTemplate)
    {
        $this->highlightTemplate = $highlightTemplate;

        return $this;
    }

    /**
     * @return string
     */
    public function getHighlightTemplate()
    {
        return $this->highlightTemplate;
    }

    /**
     * @param string     $word
     * @param ExternalId $externalId
     * @param float      $weight
     * @param int[]      $positions
     *
     * @throws ImmutableException
     */
    public function addWordWeight($word, ExternalId $externalId, $weight, $positions = [])
    {
        if ($this->isFrozen) {
            throw new ImmutableException('One cannot mutate a search result after obtaining its content.');
        }

        $serializedExtId = $externalId->toString();

        if (!isset ($this->data[$serializedExtId][$word])) {
            $this->data[$serializedExtId][$word]      = $weight;
            $this->positions[$serializedExtId][$word] = $positions;
        } else {
            $this->data[$serializedExtId][$word]      += $weight;
            $this->positions[$serializedExtId][$word] = array_merge($this->positions[$serializedExtId][$word], $positions);
        }

        if (empty($positions)) {
            $this->trace->addKeywordWeight($word, $serializedExtId, $weight);
        } else {
            $this->trace->addWordWeight($word, $serializedExtId, $weight, $positions);
        }
    }

    /**
     * @param string     $word1
     * @param string     $word2
     * @param ExternalId $externalId
     * @param float      $weight
     * @param int        $distance
     *
     * @throws ImmutableException
     */
    public function addNeighbourWeight($word1, $word2, ExternalId $externalId, $weight, $distance)
    {
        if ($this->isFrozen) {
            throw new ImmutableException('One cannot mutate a search result after obtaining its content.');
        }

        $serializedExtId = $externalId->toString();

        $this->data[$serializedExtId]['*n_' . $word1 . '_' . $word2] = $weight;

        $this->trace->addNeighbourWeight($word1, $word2, $serializedExtId, $weight, $distance);
    }

    /**
     * @param ExternalId $externalId
     * @param float      $ratio
     *
     * @throws ImmutableException
     * @throws UnknownIdException
     */
    public function setRelevanceRatio(ExternalId $externalId, $ratio)
    {
        if (!is_numeric($ratio)) {
            throw new InvalidArgumentException(sprintf('Ratio must be a float value. "%s" given.', print_r($ratio, true)));
        }

        if (!$this->isFrozen) {
            throw new ImmutableException('One cannot provide external relevance ratios before freezing the result set.');
        }

        if ($this->sortedRelevance !== null) {
            throw new ImmutableException('One cannot set relevance ratios after sorting the result set.');
        }

        $serializedExtId = $externalId->toString();
        if (!isset($this->data[$serializedExtId])) {
            throw UnknownIdException::createResultMissingExternalId($externalId);
        }

        $this->externalRelevanceRatios[$serializedExtId] = $ratio;
    }

    /**
     * @return array
     * @throws ImmutableException
     */
    public function getSortedRelevanceByExternalId()
    {
        if (!$this->isFrozen) {
            throw new ImmutableException('One cannot read a result before freezing it.');
        }

        if ($this->sortedRelevance !== null) {
            return $this->sortedRelevance;
        }

        $this->sortedRelevance = [];
        foreach ($this->data as $serializedExtId => $stat) {
            $relevance = array_sum($stat);
            if (isset($this->externalRelevanceRatios[$serializedExtId])) {
                $relevance *= $this->externalRelevanceRatios[$serializedExtId];
            }
            $this->sortedRelevance[$serializedExtId] = $relevance;
        }

        // Order by relevance
        arsort($this->sortedRelevance);

        if ($this->limit > 0) {
            $this->sortedRelevance = array_slice(
                $this->sortedRelevance,
                $this->offset,
                $this->limit
            );
        }

        return $this->sortedRelevance;
    }

    /**
     * @throws ImmutableException
     */
    public function removeDataWithoutToc()
    {
        if ($this->sortedRelevance !== null) {
            throw new ImmutableException('One cannot remove results after sorting the result set.');
        }

        foreach ($this->data as $serializedExtId => $stat) {
            if (!isset($this->items[$serializedExtId])) {
                // We found a result just before it was deleted.
                // Remove it from the result set.
                unset(
                    $this->data[$serializedExtId],
                    $this->externalRelevanceRatios[$serializedExtId],
                    $this->items[$serializedExtId],
                    $this->positions[$serializedExtId]
                );
            }
        }
    }

    /**
     * @return array
     * @throws ImmutableException
     */
    public function getFoundWordPositionsByExternalId()
    {
        if (!$this->isFrozen) {
            throw new ImmutableException('One cannot read a result before freezing it.');
        }

        return $this->positions;
    }

    /**
     * Finishes the process of building the ResultSet.
     *
     * @return $this;
     */
    public function freeze()
    {
        $this->isFrozen = true;

        return $this;
    }

    public function attachToc(TocEntryWithExternalId $tocEntryWithExternalId)
    {
        $tocEntry   = $tocEntryWithExternalId->getTocEntry();
        $externalId = $tocEntryWithExternalId->getExternalId();

        $this->items[$externalId->toString()] = new ResultItem(
            $externalId->getId(),
            $externalId->getInstanceId(),
            $tocEntry->getTitle(),
            $tocEntry->getDescription(),
            $tocEntry->getDate(),
            $tocEntry->getUrl(),
            $this->highlightTemplate
        );
    }

    /**
     * @param ExternalId $externalId
     * @param Snippet    $snippet
     *
     * @throws UnknownIdException
     */
    public function attachSnippet(ExternalId $externalId, Snippet $snippet)
    {
        $serializedExtId = $externalId->toString();
        if (!isset($this->items[$serializedExtId])) {
            throw UnknownIdException::createResultMissingExternalId($externalId);
        }
        $this->items[$serializedExtId]->setSnippet($snippet);
    }

    /**
     * @return ResultItem[]
     * @throws ImmutableException
     */
    public function getItems()
    {
        $relevanceArray = $this->getSortedRelevanceByExternalId();

        $foundWords = $this->getFoundWordPositionsByExternalId();

        $result = [];
        foreach ($relevanceArray as $serializedExtId => $relevance) {
            $resultItem = $this->items[$serializedExtId];
            $resultItem
                ->setRelevance($relevance)
                ->setFoundWords(array_keys($foundWords[$serializedExtId]))
            ;
            $result[] = $resultItem;
        }

        return $result;
    }

    /**
     * @return ExternalIdCollection
     * @throws ImmutableException
     */
    public function getFoundExternalIds()
    {
        if (!$this->isFrozen) {
            throw new ImmutableException('One cannot read a result before freezing it.');
        }

        return ExternalIdCollection::fromStringArray(array_keys($this->data));
    }

    /**
     * @return ExternalIdCollection
     * @throws ImmutableException
     */
    public function getSortedExternalIds()
    {
        return ExternalIdCollection::fromStringArray(array_keys($this->getSortedRelevanceByExternalId()));
    }

    /**
     * @return array
     * @throws UnknownIdException
     * @throws ImmutableException
     */
    public function getTrace()
    {
        if (!$this->isFrozen) {
            throw new ImmutableException('One cannot obtain a trace before freezing the result set.');
        }

        $traceArray     = $this->trace->toArray();
        $relevanceArray = $this->getSortedRelevanceByExternalId();

        $result = [];
        foreach ($relevanceArray as $serializedExtId => $relevance) {
            if (!isset($this->items[$serializedExtId])) {
                throw UnknownIdException::createResultMissingExternalId(ExternalId::fromString($serializedExtId));
            }

            $result[$serializedExtId] = [
                'title'     => $this->items[$serializedExtId]->getTitle(),
                'relevance' => $relevance,
            ];

            if (isset($this->externalRelevanceRatios[$serializedExtId])) {
                $result[$serializedExtId]['externalRelevanceRatio'] = $this->externalRelevanceRatios[$serializedExtId];
            }

            $result[$serializedExtId]['trace'] = $traceArray[$serializedExtId];
        }

        return $result;
    }
}
