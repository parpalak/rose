<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Search\Storage\Database;

use S2\Search\Entity\TocEntry;
use S2\Search\Storage\StorageReadInterface;
use S2\Search\Storage\StorageWriteInterface;

/**
 * Class PdoStorage
 */
class PdoStorage implements StorageWriteInterface, StorageReadInterface
{
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
	protected $cachedWordIds = [];

	/**
	 * @var array
	 */
	protected $tocCache;

	/**
	 * PdoStorage constructor.
	 *
	 * @param \PDO   $pdo
	 * @param string $prefix
	 */
	public function __construct(\PDO $pdo, $prefix = 's2_search_engine_')
	{
		$this->pdo    = $pdo;
		$this->prefix = $prefix;
	}

	/**
	 * Drops and creates index tables.
	 */
	public function erase()
	{
		$this->tocCache      = [];
		$this->cachedWordIds = [];

		$this->pdo->exec('DROP TABLE IF EXISTS ' . $this->prefix . 'toc;');
		$this->pdo->exec('CREATE TABLE ' . $this->prefix . 'toc (
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

		$this->pdo->exec('DROP TABLE IF EXISTS ' . $this->prefix . 'fulltext_index;');
		$this->pdo->exec('CREATE TABLE ' . $this->prefix . 'fulltext_index (
			word_id INT(11) UNSIGNED NOT NULL,
			toc_id INT(11) UNSIGNED NOT NULL,
			position INT(11) UNSIGNED NOT NULL,
			KEY (word_id),
			KEY (toc_id)
		) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;');

		$this->pdo->exec('DROP TABLE IF EXISTS ' . $this->prefix . 'word;');
		$this->pdo->exec('CREATE TABLE ' . $this->prefix . 'word (
			id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL DEFAULT "",
			PRIMARY KEY (`id`),
			KEY (name)
		) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;');

		$this->pdo->exec('DROP TABLE IF EXISTS ' . $this->prefix . 'keyword_index;');
		$this->pdo->exec('CREATE TABLE ' . $this->prefix . 'keyword_index (
			keyword VARCHAR(255) NOT NULL,
			toc_id INT(11) UNSIGNED NOT NULL,
			type INT(11) UNSIGNED NOT NULL,
			KEY (keyword),
			KEY (toc_id)
		) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;');

		$this->pdo->exec('DROP TABLE IF EXISTS ' . $this->prefix . 'keyword_multiple_index;');
		$this->pdo->exec('CREATE TABLE ' . $this->prefix . 'keyword_multiple_index (
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
			FROM ' . $this->prefix . 'fulltext_index AS f
			JOIN ' . $this->prefix . 'word AS w ON w.id = f.word_id
			JOIN ' . $this->prefix . 'toc AS t ON t.id = f.toc_id
			WHERE w.name = ?
		';

		$statement = $this->pdo->prepare($sql);
		$statement->execute([$word]);

		return $statement->fetchAll(\PDO::FETCH_COLUMN | \PDO::FETCH_GROUP);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSingleKeywordIndexByWord($word)
	{
		$sql = '
			SELECT t.external_id, k.type
			FROM ' . $this->prefix . 'keyword_index AS k
			JOIN ' . $this->prefix . 'toc AS t ON t.id = k.toc_id
			WHERE k.keyword = ?
		';

		$st = $this->pdo->prepare($sql);
		$st->execute([$word]);

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
			FROM ' . $this->prefix . 'keyword_multiple_index AS k
			JOIN ' . $this->prefix . 'toc AS t ON t.id = k.toc_id
			WHERE k.keyword LIKE ? ESCAPE \'=\'
		';

		$statement = $this->pdo->prepare($sql);
		$statement->execute(['% ' . $this->escapeLike($string, '=') . ' %']);

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
	private function escapeLike($s, $e) {
		return str_replace(array($e, '_', '%'), array($e.$e, $e.'_', $e.'%'), $s);
	}

	/**
	 * {@inheritdoc}
	 */
	public function findTocByTitle($string)
	{
		return $this->getTocEntries(['title' => $string]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeFromIndex($externalId)
	{
		$tocId = $this->getInternalIdFromExternalId($externalId);

		$st = $this->pdo->prepare('DELETE FROM ' . $this->prefix . 'fulltext_index WHERE toc_id = ?');
		$st->execute([$tocId]);

		$st = $this->pdo->prepare('DELETE FROM ' . $this->prefix . 'keyword_index WHERE toc_id = ?');
		$st->execute([$tocId]);

		$st = $this->pdo->prepare('DELETE FROM ' . $this->prefix . 'keyword_multiple_index WHERE toc_id = ?');
		$st->execute([$tocId]);
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

		$data = [];
		foreach ($words as $position => $word) {
			$data[] = $wordIds[$word] . ',' . $internalId . ',' . ((int) $position);
		}

		$sql = 'INSERT INTO ' . $this->prefix . 'fulltext_index (word_id, toc_id, position) VALUES ( ' . implode('),(', $data) . ')';

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

		$data = [];
		foreach ([$word] as $keyword) {// Ready to bulk insert
			$data[] = $this->pdo->quote($keyword) . ',' . $internalId . ',' . ((int) $type);
		}

		$sql = 'INSERT INTO ' . $this->prefix . 'keyword_index (keyword, toc_id, type) VALUES ( ' . implode('),(', $data) . ')';

		$st = $this->pdo->prepare($sql);
		$st->execute();
	}

	/**
	 * {@inheritdoc}
	 */
	public function addToMultipleKeywordIndex($string, $externalId, $type)
	{
		$internalId = $this->getInternalIdFromExternalId($externalId);

		$data = [];
		foreach ([$string] as $keyword) {// Ready to bulk insert
			$data[] = $this->pdo->quote($keyword) . ',' . $internalId . ',' . ((int) $type);
		}

		$sql = 'INSERT INTO ' . $this->prefix . 'keyword_multiple_index (keyword, toc_id, type) VALUES ( ' . implode('),(', $data) . ')';

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
			$sql = 'INSERT INTO ' . $this->prefix . 'toc (external_id, title, description, added_at, url, hash) VALUES (?, ?, ?, ?, ?, ?)';

			$statement = $this->pdo->prepare($sql);
			$statement->execute([
				$externalId,
				$entry->getTitle(),
				$entry->getDescription(),
				$entry->getFormattedDate(),
				$entry->getUrl(),
				$entry->getHash(),
			]);

			$sql = 'SELECT id FROM ' . $this->prefix . 'toc WHERE external_id = ?';

			$statement = $this->pdo->prepare($sql);
			$statement->execute([$externalId]);
			$entry->setInternalId($statement->fetch(\PDO::FETCH_COLUMN));

			$this->tocCache[$externalId] = $entry;
		}
		else {
			$sql = 'UPDATE ' . $this->prefix . 'toc SET title = ?, description = ?, added_at = ?, url = ?, hash = ? WHERE id = ?';

			$statement = $this->pdo->prepare($sql);
			$statement->execute([
				$entry->getTitle(),
				$entry->getDescription(),
				$entry->getDate()->format('c'),
				$entry->getUrl(),
				$entry->getHash(),
				$tocId,
			]);
		}
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
			DELETE FROM ' . $this->prefix . 'toc
			WHERE external_id = ?
		';
		$st  = $this->pdo->prepare($sql);
		$st->execute([$externalId]);

		unset($this->tocCache[$externalId]);
	}

	/**
	 * @param string[] $words
	 *
	 * @return int
	 */
	private function getWordIds(array $words)
	{
		$knownWords   = [];
		$unknownWords = [];
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

		$sql = 'INSERT INTO ' . $this->prefix . 'word (name) VALUES ("' . implode(
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
			FROM ' . $this->prefix . 'word AS w
			WHERE name IN (' . implode(',', array_fill(0, count($words), '?')) . ')
		';

		$st = $this->pdo->prepare($sql);
		$st->execute(array_values($words));

		return $st->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_COLUMN | \PDO::FETCH_UNIQUE) ?: [];
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	private function getTocEntries(array $params = [])
	{
		if (isset($params['title'])) {
			$sql = '
				SELECT *
				FROM ' . $this->prefix . 'toc AS t
				WHERE t.title LIKE ? ESCAPE \'=\'
			';

			$st = $this->pdo->prepare($sql);
			$st->execute(['%' . $this->escapeLike($params['title'], '=') . '%']);
		}
		else {
			$sql = 'SELECT * FROM ' . $this->prefix . 'toc AS t';

			$st = $this->pdo->prepare($sql);
			$st->execute();
		}

		$result = [];
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
