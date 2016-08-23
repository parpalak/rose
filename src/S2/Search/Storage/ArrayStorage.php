<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Search\Storage;

use S2\Search\Entity\TocEntry;
use S2\Search\Exception\UnknownIdException;

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
	public function getFulltextByWord($word)
	{
		return $this->makeKeysExternalIds($this->fulltextProxy->getByWord($word));
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
	public function addToFulltext($word, $externalId, $position)
	{
		$id = $this->internalIdFromExternalId($externalId);
		$this->fulltextProxy->addWord($word, $id, $position);
	}

	/**
	 * {@inheritdoc}
	 */
	public function cleanup()
	{
		$threshold = max(count($this->toc) * 0.5, 100);

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

		foreach ($this->indexBaseKeywords as &$data) {
			if (isset($data[$internalId])) {
				unset($data[$internalId]);
			}
		}
		unset($data);

		foreach ($this->indexMultiKeywords as &$data) {
			if (isset($data[$internalId])) {
				unset($data[$internalId]);
			}
		}
		unset($data);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSingleKeywordIndexByWord($word)
	{
		if (!isset($this->indexSingleKeywords[$word])) {
			return [];
		}

		return $this->makeKeysExternalIds($this->indexSingleKeywords[$word]);
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
			throw new UnknownIdException('External id "%s" not found in index.');
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
		}
		catch (UnknownIdException $e) {
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
		unset($this->externalIdMap[$internalId]);
		unset($this->toc[$externalId]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function findTocByTitle($string)
	{
		$result = [];
		foreach ($this->toc as $externalId => $entry) {
			if (strpos(mb_strtolower($entry->getTitle()), mb_strtolower($string)) !== false) {
				$result[$externalId] = $entry;
			}
		}

		return $result;
	}
}
