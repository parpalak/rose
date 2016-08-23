<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Search\Entity;

use S2\Search\Helper\Helper;

/**
 * Class Result
 */
class Result
{
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
	protected $state = [];

	/**
	 * @var array
	 */
	protected $profilePoints = [];

	/**
	 * @var bool
	 */
	protected $isFrozen = false;

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
	 * @param bool $isDebug
	 */
	public function __construct($isDebug)
	{
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
			throw new \RuntimeException('One cannot mutate a search result after obtaining its content.');
		}

		if (!isset ($this->state[$externalId][$word])) {
			$this->state[$externalId][$word] = $weight;
		}
		else {
			$this->state[$externalId][$word] += $weight;
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
			throw new \RuntimeException('One cannot mutate a search result after obtaining its content.');
		}

		$this->state[$externalId]['*n_' . $word] = $weight;
	}

	public function getWeightByExternalId()
	{
		if ($this->foundWeights !== null) {
			return $this->foundWeights;
		}

		$this->isFrozen = true;

		$this->foundWeights = [];
		foreach ($this->state as $externalId => $stat) {
			$this->foundWeights[$externalId] = array_sum($stat);
		}

		// Order by weight
		arsort($this->foundWeights);

		return $this->foundWeights;
	}

	/**
	 * @return array
	 */
	public function getFoundWordsByExternalId()
	{
		if ($this->foundWords !== null) {
			return $this->foundWords;
		}

		$this->isFrozen = true;

		$this->foundWords = [];
		foreach ($this->state as $externalId => $stat) {
			foreach ($stat as $word => $weight) {
				if (0 !== strpos($word, '*n_')) {
					$this->foundWords[$externalId][] = $word;
				}
			}
		}

		return $this->foundWords;
	}
}
