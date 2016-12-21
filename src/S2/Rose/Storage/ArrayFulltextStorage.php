<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

/**
 * Class ArrayFulltextStorage
 */
class ArrayFulltextStorage implements FulltextProxyInterface
{
	/**
	 * @var array
	 */
	protected $fulltextIndex = array();

	/**
	 * @return array
	 */
	public function getFulltextIndex()
	{
		return $this->fulltextIndex;
	}

	/**
	 * @param array $fulltextIndex
	 *
	 * @return ArrayFulltextStorage
	 */
	public function setFulltextIndex(array $fulltextIndex = null)
	{
		$this->fulltextIndex = $fulltextIndex;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getByWord($word)
	{
		if (!isset($this->fulltextIndex[$word])) {
			return array();
		}

		$result = array();
		foreach ($this->fulltextIndex[$word] as $id => $entries) {
			if (is_int($entries)) {
				$result[$id][] = $entries;
			}
			else {
				$entries = explode('|', $entries);
				foreach ($entries as $position) {
					$result[$id][] = base_convert($position, 36, 10);
				}
			}
		}

		return $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addWord($word, $id, $position)
	{
		$word = (string) $word;
		if ($word === '') {
			return;
		}

		if (isset($this->fulltextIndex[$word][$id])) {
			$value = $this->fulltextIndex[$word][$id];
			if (is_int($value)) {
				// There was the only one position, but it's no longer the case.
				// Convert to the 36-based number system.
				$this->fulltextIndex[$word][$id] = base_convert($value, 10, 36).'|'.base_convert($position, 10, 36);
			}
			else {
				// Appending
				$this->fulltextIndex[$word][$id] = $value.'|'.base_convert($position, 10, 36);
			}
		}
		else {
			// If there is the only one position in index, the position is stored as decimal number
			$this->fulltextIndex[$word][$id] = $position;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeWord($word)
	{
		unset($this->fulltextIndex[$word]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFrequentWords($threshold)
	{
		$result = array();
		$link = &$this->fulltextIndex; // for memory optimization
		foreach ($this->fulltextIndex as $word => $stat) {
			// Drop fulltext frequent or empty items
			$num = count($stat);
			if ($num > $threshold) {
				$result[$word] = $num;
			}
		}

		return $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeById($id)
	{
		foreach ($this->fulltextIndex as &$data) {
			if (isset($data[$id])) {
				unset($data[$id]);
			}
		}
		unset($data);
	}
}
