<?php
/**
 * @copyright 2016-2018 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

use S2\Rose\Exception\ImmutableException;
use S2\Rose\Exception\RuntimeException;
use S2\Rose\Exception\UnknownIdException;
use S2\Rose\Helper\Helper;

/**
 * Class Result
 */
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
     * @var
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
     * Result constructor.
     *
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

        $this->profilePoints[] = Helper::getProfilePoint($message, -$this->startedAt + ($this->startedAt = microtime(1)));
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
     * @param string $word
     * @param string $externalId
     * @param float  $weight
     * @param int[]  $positions
     *
     * @throws \S2\Rose\Exception\ImmutableException
     */
    public function addWordWeight($word, $externalId, $weight, $positions = [])
    {
        if ($this->isFrozen) {
            throw new ImmutableException('One cannot mutate a search result after obtaining its content.');
        }

        if (!isset ($this->data[$externalId][$word])) {
            $this->data[$externalId][$word]      = $weight;
            $this->positions[$externalId][$word] = $positions;
        } else {
            $this->data[$externalId][$word]      += $weight;
            $this->positions[$externalId][$word] = array_merge($this->positions[$externalId][$word], $positions);
        }

        if (empty($positions)) {
            $this->trace->addKeywordWeight($word, $externalId, $weight);
        } else {
            $this->trace->addWordWeight($word, $externalId, $weight, $positions);
        }
    }

    /**
     * @param string $word1
     * @param string $word2
     * @param string $externalId
     * @param float  $weight
     * @param int    $distance
     *
     * @throws \S2\Rose\Exception\ImmutableException
     */
    public function addNeighbourWeight($word1, $word2, $externalId, $weight, $distance)
    {
        if ($this->isFrozen) {
            throw new ImmutableException('One cannot mutate a search result after obtaining its content.');
        }

        $this->data[$externalId]['*n_' . $word1 . '_' . $word2] = $weight;

        $this->trace->addNeighbourWeight($word1, $word2, $externalId, $weight, $distance);
    }

    /**
     * @param string $externalId
     * @param float  $ratio
     *
     * @throws \S2\Rose\Exception\UnknownIdException
     * @throws \S2\Rose\Exception\RuntimeException
     * @throws \S2\Rose\Exception\ImmutableException
     */
    public function setRelevanceRatio($externalId, $ratio)
    {
        if (!$this->isFrozen) {
            throw new ImmutableException('One cannot provide external relevance ratios before freezing the result set.');
        }

        if ($this->sortedRelevance !== null) {
            throw new ImmutableException('One cannot set relevance ratios after sorting the result set.');
        }

        if (!isset($this->data[$externalId])) {
            throw UnknownIdException::createResultMissingExternalId($externalId);
        }

        if (!is_numeric($ratio)) {
            throw new RuntimeException(sprintf('Ratio must be a float value. "%s" given.', print_r($ratio, true)));
        }

        $this->externalRelevanceRatios[$externalId] = $ratio;
    }

    /**
     * @return array
     * @throws \S2\Rose\Exception\ImmutableException
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
        foreach ($this->data as $externalId => $stat) {
            $relevance = array_sum($stat);
            if (isset($this->externalRelevanceRatios[$externalId])) {
                $relevance *= $this->externalRelevanceRatios[$externalId];
            }
            $this->sortedRelevance[$externalId] = $relevance;
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
     * @param string $externalId
     *
     * @throws \S2\Rose\Exception\ImmutableException
     */
    public function removeByExternalId($externalId)
    {
        if ($this->sortedRelevance !== null) {
            throw new ImmutableException('One cannot remove results after sorting the result set.');
        }

        unset(
            $this->data[$externalId],
            $this->externalRelevanceRatios[$externalId],
            $this->items[$externalId],
            $this->positions[$externalId]
        );
    }

    /**
     * @return array
     * @throws \S2\Rose\Exception\ImmutableException
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

    /**
     * @param string   $externalId
     * @param TocEntry $tocEntry
     */
    public function attachToc($externalId, TocEntry $tocEntry)
    {
        $this->items[$externalId] = new ResultItem(
            $tocEntry->getTitle(),
            $tocEntry->getDescription(),
            $tocEntry->getDate(),
            $tocEntry->getUrl(),
            $this->highlightTemplate
        );
    }

    /**
     * @param string  $externalId
     * @param Snippet $snippet
     *
     * @throws \S2\Rose\Exception\UnknownIdException
     */
    public function attachSnippet($externalId, Snippet $snippet)
    {
        if (!isset($this->items[$externalId])) {
            throw UnknownIdException::createResultMissingExternalId($externalId);
        }
        $this->items[$externalId]->setSnippet($snippet);
    }

    /**
     * @return ResultItem[]
     * @throws \S2\Rose\Exception\ImmutableException
     */
    public function getItems()
    {
        $relevanceArray = $this->getSortedRelevanceByExternalId();

        $foundWords = $this->getFoundWordPositionsByExternalId();

        $result = [];
        foreach ($relevanceArray as $externalId => $relevance) {
            $resultItem = $this->items[$externalId];
            $resultItem
                ->setRelevance($relevance)
                ->setFoundWords(array_keys($foundWords[$externalId]))
            ;
            $result[$externalId] = $resultItem;
        }

        return $result;
    }

    /**
     * @return string[]
     * @throws \S2\Rose\Exception\ImmutableException
     */
    public function getFoundExternalIds()
    {
        if (!$this->isFrozen) {
            throw new ImmutableException('One cannot read a result before freezing it.');
        }

        return array_keys($this->data);
    }

    /**
     * @return string[]
     * @throws \S2\Rose\Exception\ImmutableException
     */
    public function getSortedExternalIds()
    {
        return array_keys($this->getSortedRelevanceByExternalId());
    }

    /**
     * @return array
     * @throws \S2\Rose\Exception\UnknownIdException
     * @throws \S2\Rose\Exception\ImmutableException
     */
    public function getTrace()
    {
        if (!$this->isFrozen) {
            throw new ImmutableException('One cannot obtain a trace before freezing the result set.');
        }

        $traceArray     = $this->trace->toArray();
        $relevanceArray = $this->getSortedRelevanceByExternalId();

        $result = [];
        foreach ($relevanceArray as $externalId => $relevance) {
            if (!isset($this->items[$externalId])) {
                throw UnknownIdException::createResultMissingExternalId($externalId);
            }

            $result[$externalId] = [
                'title'     => $this->items[$externalId]->getTitle(),
                'relevance' => $relevance,
            ];

            if (isset($this->externalRelevanceRatios[$externalId])) {
                $result[$externalId]['externalRelevanceRatio'] = $this->externalRelevanceRatios[$externalId];
            }

            $result[$externalId]['trace'] = $traceArray[$externalId];
        }

        return $result;
    }
}
