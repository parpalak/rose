<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Search\Entity;

use S2\Search\Exception\ImmutableException;
use S2\Search\Helper\Helper;

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
	protected $items;

	/**
	 * Result cache
	 *
	 * @var array
	 */
	protected $foundWeights;

	/**
	 * Result cache
	 *
	 * @var array
	 */
	protected $foundWords;

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
	 * @return array
	 */
	public function getWeightByExternalId()
	{
		if (!$this->isFrozen) {
			throw new ImmutableException('One cannot read a result before freezing it.');
		}

		if ($this->foundWeights !== null) {
			return $this->foundWeights;
		}

		$this->foundWeights = [];
		foreach ($this->data as $externalId => $stat) {
			$this->foundWeights[$externalId] = array_sum($stat);
		}

		// Order by weight
		arsort($this->foundWeights);

		if ($this->limit > 0) {
			$this->foundWeights = array_slice(
				$this->foundWeights,
				$this->offset,
				$this->limit
			);
		}

		return $this->foundWeights;
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

		$this->foundWords = [];
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
			$tocEntry->getUrl(),
			$this->getWeightByExternalId()[$externalId]
		);
	}

	/**
	 * @param string  $externalId
	 * @param Snippet $snippet
	 */
	public function attachSnippet($externalId, Snippet $snippet)
	{
		if (!isset($this->items[$externalId])) {
			throw new \RuntimeException(sprintf('ResultSet does not contain external id "%s".', $externalId));
		}
		$this->items[$externalId]->setSnippet($snippet);
	}

	/**
	 * @return ResultItem[]
	 */
	public function getItems()
	{
		return $this->items;
	}
}
