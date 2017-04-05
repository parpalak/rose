<?php
/**
 * @copyright 2017 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

/**
 * Class FulltextResultMock
 */
class FulltextIndexContent
{
	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * @param string $word
	 * @param string $externalId
	 * @param int    $position
	 */
	public function add($word, $externalId, $position)
	{
		$this->data[$word][$externalId][] = $position;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return $this->data;
	}
}
