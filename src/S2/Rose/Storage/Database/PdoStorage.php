<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage\Database;

use S2\Rose\Entity\TocEntry;
use S2\Rose\Storage\Exception\EmptyIndexException;
use S2\Rose\Storage\StorageReadInterface;
use S2\Rose\Storage\StorageWriteInterface;

/**
 * Class PdoStorage
 */
class PdoStorage implements StorageWriteInterface, StorageReadInterface
{
	const TOC                    = 'toc';
	const WORD                   = 'word';
	const FULLTEXT_INDEX         = 'fulltext_index';
	const KEYWORD_INDEX          = 'keyword_index';
	const KEYWORD_MULTIPLE_INDEX = 'keyword_multiple_index';

	/**
	 * @var \PDO
	 */
	protected $pdo;

	/**
	 * @var string
	 */
	protected $prefix;

	/**
	 * @var array
	 */
	protected $cachedWordIds = array();

	/**
	 * @var array
	 */
	protected $tocCache;

	/**
	 * @var array
	 */
	protected $options = array(
		self::TOC                    => 'toc',
		self::WORD                   => 'word',
		self::FULLTEXT_INDEX         => 'fulltext_index',
		self::KEYWORD_INDEX          => 'keyword_index',
		self::KEYWORD_MULTIPLE_INDEX => 'keyword_multiple_index',
	);

	/**
	 * PdoStorage constructor.
	 *
	 * @param \PDO   $pdo
	 * @param string $prefix
	 * @param array  $options
	 */
	public function __construct(\PDO $pdo, $prefix = 's2_rose_', array $options = array())
	{
		$this->pdo     = $pdo;
		$this->prefix  = $prefix;
		$this->options = array_merge($this->options, $options);
	}

	/**
	 * Drops and creates index tables.
	 */
	public function erase()
	{
		$this->tocCache      = array();
		$this->cachedWordIds = array();

		$this->pdo->exec('DROP TABLE IF EXISTS ' . $this->prefix . $this->options[self::TOC] . ';');
		$this->pdo->exec('CREATE TABLE ' . $this->prefix . $this->options[self::TOC] . ' (
			id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			external_id VARCHAR(255) NOT NULL,
			title VARCHAR(255) NOT NULL DEFAULT "",
			description TEXT NOT NULL DEFAULT "",
			added_at DATETIME NULL,
			url TEXT NOT NULL DEFAULT "",
			hash VARCHAR(80) NOT NULL DEFAULT "",
			PRIMARY KEY (`id`),
			UNIQUE KEY (external_id)
		) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;');

		$this->pdo->exec('DROP TABLE IF EXISTS ' . $this->prefix . $this->options[self::FULLTEXT_INDEX] . ';');
		$this->pdo->exec('CREATE TABLE ' . $this->prefix . $this->options[self::FULLTEXT_INDEX] . ' (
			word_id INT(11) UNSIGNED NOT NULL,
			toc_id INT(11) UNSIGNED NOT NULL,
			position INT(11) UNSIGNED NOT NULL,
			KEY (word_id),
			KEY (toc_id)
		) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;');

		$this->pdo->exec('DROP TABLE IF EXISTS ' . $this->prefix . $this->options[self::WORD] . ';');
		$this->pdo->exec('CREATE TABLE ' . $this->prefix . $this->options[self::WORD] . ' (
			id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL DEFAULT "",
			PRIMARY KEY (`id`),
			KEY (name)
		) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;');

		$this->pdo->exec('DROP TABLE IF EXISTS ' . $this->prefix . $this->options[self::KEYWORD_INDEX] . ';');
		$this->pdo->exec('CREATE TABLE ' . $this->prefix . $this->options[self::KEYWORD_INDEX] . ' (
			keyword VARCHAR(255) NOT NULL,
			toc_id INT(11) UNSIGNED NOT NULL,
			type INT(11) UNSIGNED NOT NULL,
			KEY (keyword),
			KEY (toc_id)
		) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;');

		$this->pdo->exec('DROP TABLE IF EXISTS ' . $this->prefix . $this->options[self::KEYWORD_MULTIPLE_INDEX] . ';');
		$this->pdo->exec('CREATE TABLE ' . $this->prefix . $this->options[self::KEYWORD_MULTIPLE_INDEX] . ' (
			keyword VARCHAR(255) NOT NULL,
			toc_id INT(11) UNSIGNED NOT NULL,
			type INT(11) UNSIGNED NOT NULL,
			KEY (keyword),
			KEY (toc_id)
		) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFulltextByWord($word)
	{
		$sql = '
			SELECT t.external_id, f.position
			FROM ' . $this->prefix . $this->options[self::FULLTEXT_INDEX] . ' AS f
			JOIN ' . $this->prefix . $this->options[self::WORD] . ' AS w ON w.id = f.word_id
			JOIN ' . $this->prefix . $this->options[self::TOC] . ' AS t ON t.id = f.toc_id
			WHERE w.name = ?
		';

		try {
			$statement = $this->pdo->prepare($sql);
			$statement->execute(array($word));
		}
		catch (\PDOException $e) {
			if ($e->getCode() === '42S02') {
				throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
			}
			throw $e;
		}

		return $statement->fetchAll(\PDO::FETCH_COLUMN | \PDO::FETCH_GROUP);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSingleKeywordIndexByWord($word)
	{
		$sql = '
			SELECT t.external_id, k.type
			FROM ' . $this->prefix . $this->options[self::KEYWORD_INDEX] . ' AS k
			JOIN ' . $this->prefix . $this->options[self::TOC] . ' AS t ON t.id = k.toc_id
			WHERE k.keyword = ?
		';

		try {
			$st = $this->pdo->prepare($sql);
			$st->execute(array($word));
		}
		catch (\PDOException $e) {
			if ($e->getCode() === '42S02') {
				throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
			}
			throw $e;
		}

		// TODO \PDO::FETCH_UNIQUE seems to be a hack for caller. Rewrite?
		$data = $st->fetchAll(\PDO::FETCH_COLUMN | \PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);

		return $data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMultipleKeywordIndexByString($string)
	{
		$sql = '
			SELECT t.external_id, k.type
			FROM ' . $this->prefix . $this->options[self::KEYWORD_MULTIPLE_INDEX] . ' AS k
			JOIN ' . $this->prefix . $this->options[self::TOC] . ' AS t ON t.id = k.toc_id
			WHERE k.keyword LIKE ? ESCAPE \'=\'
		';

		try {
			$statement = $this->pdo->prepare($sql);
			$statement->execute(array('% ' . $this->escapeLike($string, '=') . ' %'));
		}
		catch (\PDOException $e) {
			if ($e->getCode() === '42S02') {
				throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
			}
			throw $e;
		}

		// TODO \PDO::FETCH_UNIQUE seems to be a hack for caller. Rewrite?
		$data = $statement->fetchAll(\PDO::FETCH_COLUMN | \PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);

		return $data;
	}

	/**
	 * @see http://stackoverflow.com/questions/3683746/escaping-mysql-wild-cards
	 *
	 * @param string $s
	 * @param string $e
	 *
	 * @return mixed
	 */
	private function escapeLike($s, $e)
	{
		return str_replace(array($e, '_', '%'), array($e . $e, $e . '_', $e . '%'), $s);
	}

	/**
	 * {@inheritdoc}
	 */
	public function findTocByTitle($string)
	{
		return $this->getTocEntries(array('title' => $string));
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeFromIndex($externalId)
	{
		$tocId = $this->getInternalIdFromExternalId($externalId);

		$st = $this->pdo->prepare('DELETE FROM ' . $this->prefix . $this->options[self::FULLTEXT_INDEX] . ' WHERE toc_id = ?');
		$st->execute(array($tocId));

		$st = $this->pdo->prepare('DELETE FROM ' . $this->prefix . $this->options[self::KEYWORD_INDEX] . ' WHERE toc_id = ?');
		$st->execute(array($tocId));

		$st = $this->pdo->prepare('DELETE FROM ' . $this->prefix . $this->options[self::KEYWORD_MULTIPLE_INDEX] . ' WHERE toc_id = ?');
		$st->execute(array($tocId));
	}

	/**
	 * {@inheritdoc}
	 */
	public function addToFulltext(array $words, $externalId)
	{
		if (empty($words)) {
			return;
		}

		$internalId = $this->getInternalIdFromExternalId($externalId);
		$wordIds    = $this->getWordIds($words);

		$data = array();
		foreach ($words as $position => $word) {
			$data[] = $wordIds[$word] . ',' . $internalId . ',' . ((int) $position);
		}

		$sql = 'INSERT INTO ' . $this->prefix . $this->options[self::FULLTEXT_INDEX] . ' (word_id, toc_id, position) VALUES ( ' . implode('),(', $data) . ')';

		$st = $this->pdo->prepare($sql);
		$st->execute();
	}

	/**
	 * {@inheritdoc}
	 */
	public function isExcluded($word)
	{
		// Nothing is excluded in current DB storage implementation.
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addToSingleKeywordIndex($word, $externalId, $type)
	{
		$internalId = $this->getInternalIdFromExternalId($externalId);

		$data = array();
		foreach (array($word) as $keyword) {// Ready for bulk insert
			$data[] = $this->pdo->quote($keyword) . ',' . $internalId . ',' . ((int) $type);
		}

		$sql = 'INSERT INTO ' . $this->prefix . $this->options[self::KEYWORD_INDEX] . ' (keyword, toc_id, type) VALUES ( ' . implode('),(', $data) . ')';

		$st = $this->pdo->prepare($sql);
		$st->execute();
	}

	/**
	 * {@inheritdoc}
	 */
	public function addToMultipleKeywordIndex($string, $externalId, $type)
	{
		$internalId = $this->getInternalIdFromExternalId($externalId);

		$data = array();
		foreach (array($string) as $keyword) {// Ready for bulk insert
			$data[] = $this->pdo->quote($keyword) . ',' . $internalId . ',' . ((int) $type);
		}

		$sql = 'INSERT INTO ' . $this->prefix . $this->options[self::KEYWORD_MULTIPLE_INDEX] . ' (keyword, toc_id, type) VALUES ( ' . implode('),(', $data) . ')';

		$st = $this->pdo->prepare($sql);
		$st->execute();
	}

	/**
	 * {@inheritdoc}
	 */
	public function addItemToToc(TocEntry $entry, $externalId)
	{
		$tocId = $this->getInternalIdFromExternalId($externalId);
		if (!$tocId) {
			$sql = 'INSERT INTO ' . $this->prefix . $this->options[self::TOC] . ' (external_id, title, description, added_at, url, hash) VALUES (?, ?, ?, ?, ?, ?)';

			$statement = $this->pdo->prepare($sql);
			$statement->execute(array(
				$externalId,
				$entry->getTitle(),
				$entry->getDescription(),
				$entry->getFormattedDate(),
				$entry->getUrl(),
				$entry->getHash(),
			));

			$sql = 'SELECT id FROM ' . $this->prefix . $this->options[self::TOC] . ' WHERE external_id = ?';

			$statement = $this->pdo->prepare($sql);
			$statement->execute(array($externalId));
			$entry->setInternalId($statement->fetch(\PDO::FETCH_COLUMN));
		}
		else {
			$sql = 'UPDATE ' . $this->prefix . $this->options[self::TOC] . ' SET title = ?, description = ?, added_at = ?, url = ?, hash = ? WHERE id = ?';

			$statement = $this->pdo->prepare($sql);
			$statement->execute(array(
				$entry->getTitle(),
				$entry->getDescription(),
				$entry->getFormattedDate(),
				$entry->getUrl(),
				$entry->getHash(),
				$tocId,
			));
		}

		$this->tocCache[$externalId] = $entry;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTocByExternalId($externalId)
	{
		$cache = $this->getTocCache();

		if (isset($cache[$externalId])) {
			return $cache[$externalId];
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTocSize()
	{
		return count($this->getTocCache());
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeFromToc($externalId)
	{
		$sql = '
			DELETE FROM ' . $this->prefix . $this->options[self::TOC] . '
			WHERE external_id = ?
		';

		try {
			$st  = $this->pdo->prepare($sql);
			$st->execute(array($externalId));
		}
		catch (\PDOException $e) {
			if ($e->getCode() === '42S02') {
				throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
			}
			throw $e;
		}
		unset($this->tocCache[$externalId]);
	}

	/**
	 * @param string[] $words
	 *
	 * @return int[]
	 */
	private function getWordIds(array $words)
	{
		$knownWords   = array();
		$unknownWords = array();
		foreach ($words as $k => $word) {
			if (isset($this->cachedWordIds[$word])) {
				$knownWords[$word] = $this->cachedWordIds[$word];
			}
			else {
				$unknownWords[$word] = 1;
			}
		}

		if (empty($unknownWords)) {
			return $knownWords;
		}

		$ids = $this->fetchIdsFromWords(array_keys($unknownWords));
		foreach ($ids as $word => $id) {
			$this->cachedWordIds[$word] = $id;
			$knownWords[$word]          = $id;
			unset($unknownWords[$word]);
		}

		if (empty($unknownWords)) {
			return $knownWords;
		}

		$sql = 'INSERT INTO ' . $this->prefix . $this->options[self::WORD] . ' (name) VALUES ("' . implode(
				'"),("',
				array_map(function ($x) {
					return addslashes($x);
				}, array_keys($unknownWords))
			) . '")';
		$st  = $this->pdo->prepare($sql);
		$st->execute();

		$ids = $this->fetchIdsFromWords(array_keys($unknownWords));
		foreach ($ids as $word => $id) {
			$this->cachedWordIds[$word] = $id;
			$knownWords[$word]          = $id;
			unset($unknownWords[$word]);
		}

		if (empty($unknownWords)) {
			return $knownWords;
		}

		throw new \LogicException('Inserted rows not found.');
	}

	/**
	 * @param $externalId
	 *
	 * @return int|null
	 */
	private function getInternalIdFromExternalId($externalId)
	{
		$tocEntry = $this->getTocByExternalId($externalId);

		return $tocEntry ? $tocEntry->getInternalId() : null;
	}

	/**
	 * @param string[] $words
	 *
	 * @return array
	 */
	private function fetchIdsFromWords(array $words)
	{
		$sql = '
			SELECT name, id
			FROM ' . $this->prefix . $this->options[self::WORD] . ' AS w
			WHERE name IN (' . implode(',', array_fill(0, count($words), '?')) . ')
		';

		$st = $this->pdo->prepare($sql);
		$st->execute(array_values($words));

		return $st->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_COLUMN | \PDO::FETCH_UNIQUE) ?: array();
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	private function getTocEntries(array $params = array())
	{
		try {
			if (isset($params['title'])) {
				$sql = '
					SELECT *
					FROM ' . $this->prefix . $this->options[self::TOC] . ' AS t
					WHERE t.title LIKE ? ESCAPE \'=\'
				';

				$st = $this->pdo->prepare($sql);
				$st->execute(array('%' . $this->escapeLike($params['title'], '=') . '%'));
			}
			else {
				$sql = 'SELECT * FROM ' . $this->prefix . $this->options[self::TOC] . ' AS t';

				$st = $this->pdo->prepare($sql);
				$st->execute();
			}
		}
		catch (\PDOException $e) {
			if ($e->getCode() === '42S02') {
				throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
			}
			throw $e;
		}

		$result = array();
		foreach ($st->fetchAll() as $row) {
			$tocEntry = new TocEntry(
				$row['title'],
				$row['description'],
				new \DateTime($row['added_at']),
				$row['url'],
				$row['hash']
			);
			$tocEntry->setInternalId($row['id']);

			$result[$row['external_id']] = $tocEntry;
		}

		return $result;
	}

	/**
	 * @return array
	 */
	private function getTocCache()
	{
		if ($this->tocCache === null) {
			$this->tocCache = $this->getTocEntries();
		}

		return $this->tocCache;
	}
}
