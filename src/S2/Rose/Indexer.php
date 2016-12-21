<?php
/**
 * Creates search index
 *
 * @copyright 2010-2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose;

use S2\Rose\Entity\Indexable;
use S2\Rose\Stemmer\StemmerInterface;
use S2\Rose\Storage\StorageWriteInterface;

/**
 * Class Indexer
 */
class Indexer
{
	/**
	 * @var StorageWriteInterface
	 */
	protected $storage;

	/**
	 * @var StemmerInterface
	 */
	protected $stemmer;

	/**
	 * Indexer constructor.
	 *
	 * @param StorageWriteInterface $storage
	 * @param StemmerInterface      $stemmer
	 */
	public function __construct(
		StorageWriteInterface $storage,
		StemmerInterface $stemmer
	) {
		$this->storage = $storage;
		$this->stemmer = $stemmer;
	}

	/**
	 * Cleaning up an HTML string.
	 *
	 * @param string $contents
	 *
	 * @return string
	 */
	protected static function strFromHtml($contents)
	{
		$contents = strip_tags($contents);

		$contents = mb_strtolower($contents);
		$contents = str_replace(array('&nbsp;', "\xc2\xa0"), ' ', $contents);
		$contents = preg_replace('#&[^;]{1,20};#', '', $contents);
		$contents = preg_replace('#[^\-а-яё0-9a-z\^]+#u', ' ', $contents);

		return $contents;
	}

	/**
	 * @param string $contents
	 *
	 * @return string[]
	 */
	protected static function arrayFromStr($contents)
	{
		return preg_split('#[ ]+#', $contents);
	}

	/**
	 * @param string $word
	 * @param int    $externalId
	 * @param int    $type
	 */
	protected function addKeywordToIndex($word, $externalId, $type)
	{
		if ($word === '') {
			return;
		}

		$word = str_replace('ё', 'е', $word);

		if (strpos($word, ' ') !== false) {
			$this->storage->addToMultipleKeywordIndex($word, $externalId, $type);
		}
		else {
			$this->storage->addToSingleKeywordIndex($word, $externalId, $type);
		}
	}

	/**
	 * @param string $externalId
	 * @param string $title
	 * @param string $contents
	 * @param string $keywords
	 */
	protected function addToIndex($externalId, $title, $contents, $keywords)
	{
		// Processing title
		foreach (self::arrayFromStr($title) as $word) {
			$this->addKeywordToIndex(trim($word), $externalId, Finder::TYPE_TITLE);
		}

		// Processing keywords
		foreach (explode(',', $keywords) as $item) {
			$this->addKeywordToIndex(trim($item), $externalId, Finder::TYPE_KEYWORD);
		}

		// Fulltext index
		// Remove russian ё from the fulltext index
		$words = self::arrayFromStr(str_replace('ё', 'е', $title . ' ' . str_replace(', ', ' ', $keywords) . ' ' . $contents));

		$subwords = array();

		foreach ($words as $i => &$word) {
			if ($word == '-' || $this->storage->isExcluded($word)) {
				unset($words[$i]);
				continue;
			}

			// If the word contains the hyphen, add a variant without it
			if (strlen($word) > 1 && false !== strpos($word, '-')) {
				foreach (explode('-', $word) as $k => $subword) {
					if ($subword) {
						$subwords[(string) ($i + 0.1 * $k)] = $this->stemmer->stemWord($subword);
					}
				}
			}

			$word = $this->stemmer->stemWord($word);
		}
		unset($word);

		$this->storage->addToFulltext($words, $externalId);
		$this->storage->addToFulltext($subwords, $externalId);
	}

	/**
	 * @param string $externalId
	 */
	public function removeById($externalId)
	{
		$tocEntry = $this->storage->getTocByExternalId($externalId);

		if ($tocEntry) {
			$this->storage->removeFromIndex($externalId);
			$this->storage->removeFromToc($externalId);
		}
	}

	/**
	 * @param Indexable $indexable
	 */
	public function index(Indexable $indexable)
	{
		$externalId  = $indexable->getId();
		$oldTocEntry = $this->storage->getTocByExternalId($externalId);

		$this->storage->addItemToToc($indexable->toTocEntry(), $externalId);

		if ($oldTocEntry && $oldTocEntry->getHash() === $indexable->calcHash()) {
			return;
		}

		$this->storage->removeFromIndex($externalId);
		$this->addToIndex(
			$externalId,
			self::strFromHtml($indexable->getTitle()),
			self::strFromHtml($indexable->getContent()),
			$indexable->getKeywords()
		);
	}
}
