<?php
/**
 * @copyright 2016-2018 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage\Database;

use S2\Rose\Entity\TocEntry;
use S2\Rose\Exception\InvalidArgumentException;
use S2\Rose\Exception\LogicException;
use S2\Rose\Exception\UnknownException;
use S2\Rose\Exception\UnknownIdException;
use S2\Rose\Finder;
use S2\Rose\Storage\Exception\EmptyIndexException;
use S2\Rose\Storage\Exception\InvalidEnvironmentException;
use S2\Rose\Storage\FulltextIndexContent;
use S2\Rose\Storage\StorageReadInterface;
use S2\Rose\Storage\StorageWriteInterface;
use S2\Rose\Storage\TransactionalStorageInterface;

/**
 * Class PdoStorage
 */
class PdoStorage implements
    StorageWriteInterface,
    StorageReadInterface,
    TransactionalStorageInterface
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
    protected $cachedWordIds = [];

    /**
     * @var array
     */
    protected $idMapping = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * PdoStorage constructor.
     *
     * @param \PDO   $pdo
     * @param string $prefix
     * @param array  $options
     */
    public function __construct(\PDO $pdo, $prefix = 's2_rose_', array $options = [])
    {
        $this->pdo     = $pdo;
        $this->prefix  = $prefix;
        $this->options = array_merge([
            self::TOC                    => 'toc',
            self::WORD                   => 'word',
            self::FULLTEXT_INDEX         => 'fulltext_index',
            self::KEYWORD_INDEX          => 'keyword_index',
            self::KEYWORD_MULTIPLE_INDEX => 'keyword_multiple_index',
        ], $options);
    }

    /**
     * Drops and creates index tables.
     *
     * @throws \S2\Rose\Exception\LogicException
     * @throws \S2\Rose\Exception\UnknownException
     * @throws \S2\Rose\Storage\Exception\InvalidEnvironmentException
     */
    public function erase()
    {
        $this->cachedWordIds = [];

        $charset = $this->pdo->query('SELECT @@character_set_connection')->fetchColumn();
        if ($charset !== 'utf8mb4') {
            $charset = 'utf8';
        }

        try {
            try {
                $this->dropAndCreateTables($charset, 255);
            } catch (\PDOException $e) {
                if ($charset === 'utf8mb4') {
                    // See https://stackoverflow.com/questions/30761867/mysql-error-the-maximum-column-size-is-767-bytes
                    // In certain configurations we have only 767 bytes for index.
                    // We can index only 191 = round(767/4) characters in case of 4-bytes encoding utf8mb4.
                    // I prefer not to check exception codes because there are at least two possible values
                    // of $e->errorInfo: [42000, 1071, 'Specified key was too long; max key length is 767 bytes']
                    // and ['HY000', 1709, 'Index column size too large. The maximum column size is 767 bytes.'].

                    $this->dropAndCreateTables($charset, 191);
                } else {
                    throw $e;
                }
            }

        } catch (\PDOException $e) {
            if ($e->getCode() === '42000') {
                throw new InvalidEnvironmentException($e->getMessage(), $e->getCode(), $e);
            }
            throw new UnknownException(sprintf('Unknown exception "%s" occurred while creating tables: %s', $e->getCode(), $e->getMessage()), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     * @throws \S2\Rose\Exception\LogicException
     * @throws \S2\Rose\Exception\UnknownException
     * @throws \S2\Rose\Storage\Exception\EmptyIndexException
     */
    public function fulltextResultByWords(array $words)
    {
        $result = new FulltextIndexContent();
        if (empty($words)) {
            return $result;
        }

        $sql = '
			SELECT w.name AS word, t.external_id, f.position
			FROM ' . $this->getTableName(self::FULLTEXT_INDEX) . ' AS f
			JOIN ' . $this->getTableName(self::WORD) . ' AS w ON w.id = f.word_id
			JOIN ' . $this->getTableName(self::TOC) . ' AS t ON t.id = f.toc_id
			WHERE w.name IN (' . implode(',', array_fill(0, count($words), '?')) . ')
		';

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($words);
        } catch (\PDOException $e) {
            if ($e->getCode() === '42S02') {
                throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
            }
            throw new UnknownException('Unknown exception occurred while fulltext searching:' . $e->getMessage(), $e->getCode(), $e);
        }
        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($data as $row) {
            $result->add($row['word'], $row['external_id'], $row['position']);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     * @throws \S2\Rose\Storage\Exception\EmptyIndexException
     * @throws \S2\Rose\Exception\UnknownException
     * @throws \S2\Rose\Exception\LogicException
     */
    public function getSingleKeywordIndexByWords(array $words)
    {
        $sql = '
			SELECT
				k.keyword,
				t.external_id,
				k.type,
				(
					SELECT COUNT(DISTINCT f.toc_id)
					FROM ' . $this->getTableName(self::FULLTEXT_INDEX) . ' AS f
					JOIN ' . $this->getTableName(self::WORD) . ' AS w ON w.id = f.word_id
					WHERE k.keyword = w.name
				) AS usage_num
			FROM ' . $this->getTableName(self::KEYWORD_INDEX) . ' AS k
			JOIN ' . $this->getTableName(self::TOC) . ' AS t ON t.id = k.toc_id
			WHERE k.keyword IN (' . implode(',', array_fill(0, count($words), '?')) . ')
		';

        try {
            $st = $this->pdo->prepare($sql);
            $st->execute($words);
        } catch (\PDOException $e) {
            if ($e->getCode() === '42S02') {
                throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
            }
            throw new UnknownException('Unknown exception occurred while single keywords searching:' . $e->getMessage(), $e->getCode(), $e);
        }

        $data = $st->fetchAll(\PDO::FETCH_ASSOC);

        $threshold = Finder::fulltextRateExcludeNum($this->getTocSize());
        $result    = [];
        foreach ($data as $row) {
            if ($row['type'] === Finder::TYPE_TITLE && $row['usage_num'] > $threshold) {
                continue;
            }

            // TODO Making items unique seems to be a hack for caller. Rewrite indexing using INSERT IGNORE?
            $result[$row['keyword']][$row['external_id']] = $row['type'];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     * @throws \S2\Rose\Exception\LogicException
     * @throws \S2\Rose\Exception\UnknownException
     * @throws \S2\Rose\Storage\Exception\EmptyIndexException
     */
    public function getMultipleKeywordIndexByString($string)
    {
        $sql = '
			SELECT t.external_id, k.type
			FROM ' . $this->getTableName(self::KEYWORD_MULTIPLE_INDEX) . ' AS k
			JOIN ' . $this->getTableName(self::TOC) . ' AS t ON t.id = k.toc_id
			WHERE k.keyword LIKE ? ESCAPE \'=\'
		';

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute(['% ' . $this->escapeLike($string, '=') . ' %']);
        } catch (\PDOException $e) {
            if ($e->getCode() === '42S02') {
                throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
            }
            throw new UnknownException('Unknown exception occurred while multiple keywords searching:' . $e->getMessage(), $e->getCode(), $e);
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
        return str_replace([$e, '_', '%'], [$e . $e, $e . '_', $e . '%'], $s);
    }

    /**
     * {@inheritdoc}
     * @throws \S2\Rose\Exception\InvalidArgumentException
     * @throws \S2\Rose\Exception\LogicException
     * @throws \S2\Rose\Exception\UnknownException
     * @throws \S2\Rose\Storage\Exception\EmptyIndexException
     */
    public function findTocByTitle($title)
    {
        return $this->getTocEntries(['title' => $title]);
    }

    /**
     * {@inheritdoc}
     * @throws \S2\Rose\Exception\InvalidArgumentException
     * @throws \S2\Rose\Exception\LogicException
     * @throws \S2\Rose\Exception\UnknownException
     * @throws \S2\Rose\Storage\Exception\EmptyIndexException
     */
    public function removeFromIndex($externalId)
    {
        $tocEntries = $this->getTocByExternalIds([$externalId]);
        if (count($tocEntries) === 0) {
            return;
        }

        $tocId = $tocEntries[$externalId]->getInternalId();

        try {
            $st = $this->pdo->prepare('DELETE FROM ' . $this->getTableName(self::FULLTEXT_INDEX) . ' WHERE toc_id = ?');
            $st->execute([$tocId]);

            $st = $this->pdo->prepare('DELETE FROM ' . $this->getTableName(self::KEYWORD_INDEX) . ' WHERE toc_id = ?');
            $st->execute([$tocId]);

            $st = $this->pdo->prepare('DELETE FROM ' . $this->getTableName(self::KEYWORD_MULTIPLE_INDEX) . ' WHERE toc_id = ?');
            $st->execute([$tocId]);
        } catch (\PDOException $e) {
            if (1412 === (int)$e->errorInfo[1]) {
                throw new EmptyIndexException('Storage tables has been changed in the database. Is ' . __CLASS__ . '::erase() running in another process?', 0, $e);
            }
            if ($e->getCode() === '42S02') {
                throw new EmptyIndexException('There are missing storage tables in the database. Is ' . __CLASS__ . '::erase() running in another process?', 0, $e);
            }
            throw new UnknownException('Unknown exception occurred while removing from index:' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     * @throws \S2\Rose\Exception\UnknownIdException
     * @throws \S2\Rose\Exception\LogicException
     * @throws \S2\Rose\Exception\UnknownException
     * @throws \S2\Rose\Storage\Exception\EmptyIndexException
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
            $expr        = $wordIds[$word] . ',' . $internalId . ',' . ((int)$position);
            $data[$expr] = $expr;
        }

        $sql = 'INSERT INTO ' . $this->getTableName(self::FULLTEXT_INDEX) . ' (word_id, toc_id, position) VALUES ( ' . implode('),(', $data) . ')';

        try {
            $this->pdo->exec($sql);
        } catch (\PDOException $e) {
            throw new UnknownException('Unknown exception occurred while fulltext indexing:' . $e->getMessage(), $e->getCode(), $e);
        }
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
     * @throws \S2\Rose\Exception\UnknownIdException
     * @throws \S2\Rose\Exception\UnknownException
     * @throws \S2\Rose\Exception\LogicException
     */
    public function addToSingleKeywordIndex($word, $externalId, $type)
    {
        $internalId = $this->getInternalIdFromExternalId($externalId);

        $data = [];
        foreach ([$word] as $keyword) {// Ready for bulk insert
            $data[] = $this->pdo->quote($keyword) . ',' . $internalId . ',' . ((int)$type);
        }

        $sql = 'INSERT INTO ' . $this->getTableName(self::KEYWORD_INDEX) . ' (keyword, toc_id, type) VALUES ( ' . implode('),(', $data) . ')';

        try {
            $this->pdo->exec($sql);
        } catch (\PDOException $e) {
            throw new UnknownException('Unknown exception occurred while single keyword indexing:' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     * @throws \S2\Rose\Exception\UnknownIdException
     * @throws \S2\Rose\Exception\UnknownException
     * @throws \S2\Rose\Exception\LogicException
     */
    public function addToMultipleKeywordIndex($string, $externalId, $type)
    {
        $internalId = $this->getInternalIdFromExternalId($externalId);

        $data = [];
        foreach ([$string] as $keyword) {// Ready for bulk insert
            $data[] = $this->pdo->quote($keyword) . ',' . $internalId . ',' . ((int)$type);
        }

        $sql = 'INSERT INTO ' . $this->getTableName(self::KEYWORD_MULTIPLE_INDEX) . ' (keyword, toc_id, type) VALUES ( ' . implode('),(', $data) . ')';

        try {
            $this->pdo->exec($sql);
        } catch (\PDOException $e) {
            throw new UnknownException('Unknown exception occurred while multiple keyword indexing:' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     * @throws \S2\Rose\Exception\LogicException
     */
    public function addItemToToc(TocEntry $entry, $externalId)
    {
        try {
            $tocId = $this->getInternalIdFromExternalId($externalId);
        } catch (UnknownIdException $e) {
            try {
                $sql = 'INSERT INTO ' . $this->getTableName(self::TOC) .
                    ' (external_id, title, description, added_at, url, hash) VALUES (?, ?, ?, ?, ?, ?)';

                $statement = $this->pdo->prepare($sql);
                $statement->execute([
                    $externalId,
                    $entry->getTitle(),
                    $entry->getDescription(),
                    $entry->getFormattedDate(),
                    $entry->getUrl(),
                    $entry->getHash(),
                ]);

                $internalId = $this->selectInternalId($externalId);
                $entry->setInternalId($internalId);

                $this->idMapping[$externalId] = $internalId;

                return;
            } catch (\PDOException $e) {
                if (1062 === (int)$e->errorInfo[1]) {
                    // Duplicate entry for external_id key.
                    // This is an old code used when TOC was cached inside this class.
                    // TODO Research and remove if this code is dead.
                    $tocEntries = $this->getTocByExternalIds([$externalId]);
                    if (count($tocEntries) === 0) {
                        throw new LogicException('Cannot insert a TOC entry and no previous TOC entries found.');
                    }
                    $tocId = $tocEntries[$externalId]->getInternalId();
                } elseif ($e->getCode() === '42S02') {
                    throw new EmptyIndexException('There are missing storage tables in the database. Is ' . __CLASS__ . '::erase() running in another process?', 0, $e);
                } else {
                    throw new UnknownException('Unknown exception occurred while adding to TOC:' . $e->getMessage(), $e->getCode(), $e);
                }
            }
        }

        $sql = 'UPDATE ' . $this->getTableName(self::TOC) .
            ' SET title = ?, description = ?, added_at = ?, url = ?, hash = ? WHERE id = ?';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            $entry->getTitle(),
            $entry->getDescription(),
            $entry->getFormattedDate(),
            $entry->getUrl(),
            $entry->getHash(),
            $tocId,
        ]);
        $entry->setInternalId($tocId);

        $this->idMapping[$externalId] = $tocId;
    }

    /**
     * {@inheritdoc}
     * @throws \S2\Rose\Exception\InvalidArgumentException
     * @throws \S2\Rose\Exception\LogicException
     * @throws \S2\Rose\Exception\UnknownException
     * @throws \S2\Rose\Storage\Exception\EmptyIndexException
     */
    public function getTocByExternalIds($externalIds)
    {
        return $this->getTocEntries(['ids' => $externalIds]);
    }

    /**
     * {@inheritdoc}
     * @throws \S2\Rose\Storage\Exception\EmptyIndexException
     * @throws \S2\Rose\Exception\UnknownException
     * @throws \S2\Rose\Exception\LogicException
     * @throws \S2\Rose\Exception\InvalidArgumentException
     */
    public function getTocByExternalId($externalId)
    {
        $entries = $this->getTocByExternalIds([$externalId]);

        return count($entries) > 0 ? $entries[$externalId] : null;
    }

    /**
     * {@inheritdoc}
     * @throws \S2\Rose\Exception\LogicException
     * @throws \S2\Rose\Storage\Exception\EmptyIndexException
     * @throws \S2\Rose\Exception\UnknownException
     */
    public function getTocSize()
    {
        $sql = 'SELECT count(*) FROM ' . $this->getTableName(self::TOC);

        try {
            $st     = $this->pdo->query($sql);
            $result = $st->fetch(\PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            if ($e->getCode() === '42S02') {
                throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
            }
            throw new UnknownException('Unknown exception occurred while obtaining TOC size:' . $e->getMessage(), $e->getCode(), $e);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     * @throws \S2\Rose\Exception\LogicException
     * @throws \S2\Rose\Exception\UnknownException
     * @throws \S2\Rose\Storage\Exception\EmptyIndexException
     */
    public function removeFromToc($externalId)
    {
        $sql = '
			DELETE FROM ' . $this->getTableName(self::TOC) . '
			WHERE external_id = ?
		';

        try {
            $st = $this->pdo->prepare($sql);
            $st->execute([$externalId]);
        } catch (\PDOException $e) {
            if ($e->getCode() === '42S02') {
                throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
            }
            throw new UnknownException('Unknown exception occurred while removing from TOC:' . $e->getMessage(), $e->getCode(), $e);
        }

        unset($this->idMapping[$externalId]);
    }

    /**
     * {@inheritdoc}
     */
    public function startTransaction()
    {
        $this->idMapping = [];
        $this->pdo->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function commitTransaction()
    {
        $this->pdo->commit();
        $this->idMapping = [];
    }

    /**
     * {@inheritdoc}
     */
    public function rollbackTransaction()
    {
        $this->pdo->rollBack();
    }

    /**
     * @param string[] $words
     *
     * @return int[]
     * @throws \S2\Rose\Storage\Exception\EmptyIndexException
     * @throws \S2\Rose\Exception\UnknownException
     * @throws \S2\Rose\Exception\LogicException
     */
    protected function getWordIds(array $words)
    {
        $knownWords   = [];
        $unknownWords = [];
        foreach ($words as $k => $word) {
            if (isset($this->cachedWordIds[$word])) {
                $knownWords[$word] = $this->cachedWordIds[$word];
            } else {
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

        // This query can potentially lead to duplicates in the 'word' table.
        // I've tried the unique index on the name field, but it slows down
        // select queries.
        // Now there are no duplicates due to "SELECT ... LOCK IN SHARE MODE".
        $sql = 'INSERT INTO ' . $this->getTableName(self::WORD) . ' (name) VALUES ("' . implode(
                '"),("',
                array_map(function ($x) {
                    return addslashes($x);
                }, array_keys($unknownWords))
            ) . '")';
        $this->pdo->exec($sql);

        $ids = $this->fetchIdsFromWords(array_keys($unknownWords));
        foreach ($ids as $word => $id) {
            $this->cachedWordIds[$word] = $id;
            $knownWords[$word]          = $id;
            unset($unknownWords[$word]);
        }

        if (empty($unknownWords)) {
            return $knownWords;
        }

        throw new LogicException('Inserted rows not found.');
    }

    /**
     * @param string $externalId
     *
     * @return int
     * @throws \S2\Rose\Exception\UnknownIdException
     */
    private function getInternalIdFromExternalId($externalId)
    {
        if (!isset($this->idMapping[$externalId])) {
            throw UnknownIdException::createIndexMissingExternalId($externalId);
        }

        return $this->idMapping[$externalId];
    }

    /**
     * @param string[] $words
     *
     * @return array
     * @throws \S2\Rose\Exception\LogicException
     * @throws \S2\Rose\Exception\UnknownException
     * @throws \S2\Rose\Storage\Exception\EmptyIndexException
     */
    private function fetchIdsFromWords(array $words)
    {
        $sql = '
			SELECT name, id
			FROM ' . $this->getTableName(self::WORD) . ' AS w
			WHERE name IN (' . implode(',', array_fill(0, count($words), '?')) . ')
			LOCK IN SHARE MODE
		';

        try {
            $st = $this->pdo->prepare($sql);
            $st->execute(array_values($words));
        } catch (\PDOException $e) {
            if (1412 === (int)$e->errorInfo[1]) {
                throw new EmptyIndexException('Storage tables has been changed in the database. Is ' . __CLASS__ . '::erase() running in another process?', 0, $e);
            }
            throw new UnknownException('Unknown exception occurred while reading word dictionary:' . $e->getMessage(), $e->getCode(), $e);
        }

        return $st->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_COLUMN | \PDO::FETCH_UNIQUE) ?: [];
    }

    /**
     * @param array $criteria
     *
     * @return array
     * @throws \S2\Rose\Exception\UnknownException
     * @throws \S2\Rose\Storage\Exception\EmptyIndexException
     * @throws \S2\Rose\Exception\InvalidArgumentException
     * @throws \S2\Rose\Exception\LogicException
     */
    private function getTocEntries(array $criteria = [])
    {
        try {
            if (isset($criteria['title'])) {
                $sql = '
					SELECT *
					FROM ' . $this->getTableName(self::TOC) . ' AS t
					WHERE t.title LIKE ? ESCAPE \'=\'
				';

                $st = $this->pdo->prepare($sql);
                $st->execute(['%' . $this->escapeLike($criteria['title'], '=') . '%']);
            } elseif (isset($criteria['ids'])) {
                if (!is_array($criteria['ids'])) {
                    throw new InvalidArgumentException('Ids must be an array.');
                }
                $ids = $criteria['ids'];
                if (count($ids) === 0) {
                    return [];
                }
                $sql = '
					SELECT *
					FROM ' . $this->getTableName(self::TOC) . ' AS t
					WHERE t.external_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')';

                $st = $this->pdo->prepare($sql);
                $st->execute($ids);
            } else {
                throw new InvalidArgumentException('Criteria must contain title or ids conditions.');
            }
        } catch (\PDOException $e) {
            if ($e->getCode() === '42S02') {
                throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
            }
            throw new UnknownException('Unknown exception occurred while reading TOC:' . $e->getMessage(), $e->getCode(), $e);
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
     * @param string $externalId
     *
     * @return mixed
     * @throws \S2\Rose\Exception\LogicException
     */
    private function selectInternalId($externalId)
    {
        $sql = 'SELECT id FROM ' . $this->getTableName(self::TOC) . ' WHERE external_id = ?';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([$externalId]);
        $internalId = $statement->fetch(\PDO::FETCH_COLUMN);

        return $internalId;
    }

    /**
     * @param string $key
     *
     * @return string
     * @throws \S2\Rose\Exception\LogicException
     */
    private function getTableName($key)
    {
        if (!isset($this->options[$key])) {
            throw new LogicException(sprintf('Unknown table "%s"', $key));
        }

        return $this->prefix . $this->options[$key];
    }

    /**
     * @param string $charset
     * @param int    $keyLen
     */
    private function dropAndCreateTables($charset, $keyLen)
    {
        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::TOC) . ';');
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::TOC) . ' (
			id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			external_id VARCHAR(255) NOT NULL,
			title VARCHAR(255) NOT NULL DEFAULT "",
			description TEXT NOT NULL,
			added_at DATETIME NULL,
			url TEXT NOT NULL,
			hash VARCHAR(80) NOT NULL DEFAULT "",
			PRIMARY KEY (`id`),
			UNIQUE KEY (external_id(' . $keyLen . '))
		) ENGINE=InnoDB CHARACTER SET ' . $charset);

        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::FULLTEXT_INDEX) . ';');
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::FULLTEXT_INDEX) . ' (
			word_id INT(11) UNSIGNED NOT NULL,
			toc_id INT(11) UNSIGNED NOT NULL,
			position INT(11) UNSIGNED NOT NULL,
			PRIMARY KEY (word_id, toc_id, position),
			KEY (toc_id)
		) ENGINE=InnoDB CHARACTER SET ' . $charset);

        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::WORD) . ';');
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::WORD) . ' (
			id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL DEFAULT "",
			PRIMARY KEY (`id`),
			KEY (name(' . $keyLen . '))
		) ENGINE=InnoDB CHARACTER SET ' . $charset);

        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::KEYWORD_INDEX) . ';');
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::KEYWORD_INDEX) . ' (
			keyword VARCHAR(255) NOT NULL,
			toc_id INT(11) UNSIGNED NOT NULL,
			type INT(11) UNSIGNED NOT NULL,
			KEY (keyword(' . $keyLen . ')),
			KEY (toc_id)
		) ENGINE=InnoDB CHARACTER SET ' . $charset);

        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::KEYWORD_MULTIPLE_INDEX) . ';');
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::KEYWORD_MULTIPLE_INDEX) . ' (
			keyword VARCHAR(255) NOT NULL,
			toc_id INT(11) UNSIGNED NOT NULL,
			type INT(11) UNSIGNED NOT NULL,
			KEY (keyword(' . $keyLen . ')),
			KEY (toc_id)
		) ENGINE=InnoDB CHARACTER SET ' . $charset);
    }
}
