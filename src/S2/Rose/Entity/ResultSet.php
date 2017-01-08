<?php
/**
 * @copyright 2016 Roman Parpalak
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
	protected $data = array();

	/**
	 * @var array
	 */
	protected $profilePoints = array();

	/**
	 * @var bool
	 */
	protected $isFrozen = false;

	/**
	 * @var ResultItem[]
	 */
	protected $items = array();

	/**
	 * Result cache
	 *
	 * @var array
	 */
	protected $sortedRelevance;

	/**
	 * Result cache
	 *
	 * @var array
	 */
	protected $foundWords;

	/**
	 * Relevance corrections
	 *
	 * @var array
	 */
	protected $externalRelevanceRatios = array();

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
	 * @param string $word
	 * @param string $externalId
	 * @param float  $weight
	 */
	public function addWordWeight($word, $externalId, $weight)
	{
		if ($this->isFrozen) {
			throw new ImmutableException('One cannot mutate a search result after obtaining its content.');
		}

		if (!isset ($this->data[$externalId][$word])) {
			$this->data[$externalId][$word] = $weight;
		}
		else {
			$this->data[$externalId][$word] += $weight;
		}
	}

	/**
	 * @param string $word
	 * @param int    $externalId
	 * @param float  $weight
	 */
	public function addNeighbourWeight($word, $externalId, $weight)
	{
		if ($this->isFrozen) {
			throw new ImmutableException('One cannot mutate a search result after obtaining its content.');
		}

		$this->data[$externalId]['*n_' . $word] = $weight;
	}

	/**
	 * @param string $externalId
	 * @param float  $ratio
	 */
	public function setRelevanceRatio($externalId, $ratio)
	{
		if (!$this->isFrozen) {
			throw new ImmutableException('One cannot provide external relevance ratios before freezing the result.');
		}

		if ($this->sortedRelevance !== null) {
			throw new ImmutableException('One cannot set relevance ratios after sorting the result.');
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
	 */
	public function getSortedRelevanceByExternalId()
	{
		if (!$this->isFrozen) {
			throw new ImmutableException('One cannot read a result before freezing it.');
		}

		if ($this->sortedRelevance !== null) {
			return $this->sortedRelevance;
		}

		$this->sortedRelevance = array();
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
	 * @return array
	 */
	public function getFoundWordsByExternalId()
	{
		if (!$this->isFrozen) {
			throw new ImmutableException('One cannot read a result before freezing it.');
		}

		if ($this->foundWords !== null) {
			return $this->foundWords;
		}

		$this->foundWords = array();
		foreach ($this->data as $externalId => $stat) {
			foreach ($stat as $word => $weight) {
				if (0 !== strpos($word, '*n_')) {
					$this->foundWords[$externalId][] = $word;
				}
			}
		}

		return $this->foundWords;
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
			$tocEntry->getUrl()
		);
	}

	/**
	 * @param string  $externalId
	 * @param Snippet $snippet
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
	 */
	public function getItems()
	{
		$relevanceArray = $this->getSortedRelevanceByExternalId();

		$result = array();
		foreach ($relevanceArray as $externalId => $relevance) {
			$result[$externalId] = $this->items[$externalId]->setRelevance($relevance);
		}

		return $this->items;
	}

	/**
	 * @return string[]
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
	 */
	public function getSortedExternalIds()
	{
		return array_keys($this->getSortedRelevanceByExternalId());
	}
}
