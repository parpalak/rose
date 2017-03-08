<?php
/**
 * @copyright 2017 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

/**
 * Class ResultTrace
 */
class ResultTrace
{
	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * @param string $word
	 * @param string $externalId
	 * @param float  $weight
	 * @param int[]  $positions
	 */
	public function addWordWeight($word, $externalId, $weight, $positions)
	{
		$this->data[$externalId]['fulltext ' . $word][] = sprintf('%s: match at positions [%s]', $weight, implode(', ', $positions));
	}

	/**
	 * @param string $word
	 * @param string $externalId
	 * @param float  $weight
	 */
	public function addKeywordWeight($word, $externalId, $weight)
	{
		$this->data[$externalId]['keyword ' . $word][] = sprintf('%s', $weight);
	}

	/**
	 * @param string $word
	 * @param string $externalId
	 * @param float  $weight
	 */
	public function addNeighbourWeight($word, $externalId, $weight)
	{
		$this->data[$externalId]['fulltext ' . $word][] = sprintf('%s: match is close to the previous fulltext match', $weight);
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return $this->data;
	}
}
