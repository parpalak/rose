<?php
/** @noinspection SqlDialectInspection */
/** @noinspection SqlResolve */
/** @noinspection PhpComposerExtensionStubsInspection */
/**
 * @copyright 2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage\Database;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\ExternalIdCollection;
use S2\Rose\Entity\TocEntry;
use S2\Rose\Exception\InvalidArgumentException;
use S2\Rose\Exception\RuntimeException;
use S2\Rose\Exception\UnknownException;
use S2\Rose\Storage\Exception\EmptyIndexException;
use S2\Rose\Storage\Exception\InvalidEnvironmentException;

class MysqlRepository
{
    const TOC                    = 'toc';
    const WORD                   = 'word';
    const FULLTEXT_INDEX         = 'fulltext_index';
    const KEYWORD_INDEX          = 'keyword_index';
    const KEYWORD_MULTIPLE_INDEX = 'keyword_multiple_index';

    const KEYLEN         = 255;
    const UTF8MB4_KEYLEN = 191;

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
    protected $options;

    public function __construct(\PDO $pdo, $prefix = '', array $options = [])
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
     * @throws InvalidEnvironmentException
     * @throws UnknownException
     */
    public function erase()
    {
        $charset = $this->pdo->query('SELECT @@character_set_connection')->fetchColumn();
        if ($charset !== 'utf8mb4') {
            $charset = 'utf8';
        }

        try {
            try {
                $this->dropAndCreateTables($charset, self::KEYLEN);
            } catch (\PDOException $e) {
                if ($charset === 'utf8mb4') {
                    // See https://stackoverflow.com/questions/30761867/mysql-error-the-maximum-column-size-is-767-bytes
                    // In certain configurations we have only 767 bytes for index.
                    // We can index only 191 = round(767/4) characters in case of 4-bytes encoding utf8mb4.
                    // I prefer not to check exception codes because there are at least two possible values
                    // of $e->errorInfo: [42000, 1071, 'Specified key was too long; max key length is 767 bytes']
                    // and ['HY000', 1709, 'Index column size too large. The maximum column size is 767 bytes.'].

                    $this->dropAndCreateTables($charset, self::UTF8MB4_KEYLEN);
                } else {
                    throw $e;
                }
            }

        } catch (\PDOException $e) {
            if ($e->getCode() === '42000') {
                throw new InvalidEnvironmentException($e->getMessage(), $e->getCode(), $e);
            }
            throw new UnknownException(sprintf(
                'Unknown exception "%s" occurred while creating tables: "%s".',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }
    }

    /**
     * @param string[] $words
     *
     * @throws RuntimeException
     */
    public function insertWords(array $words)
    {
        $partWords = self::getPartWords($words);

        $sql = 'INSERT IGNORE INTO ' . $this->getTableName(self::WORD) . ' (name) VALUES ("' . implode(
                '"),("',
                array_map(static function ($x) {
                    return addslashes($x);
                }, $partWords)
            ) . '")';

        try {
            $this->pdo->exec($sql);
        } catch (\PDOException $e) {
            if (1205 === (int)$e->errorInfo[1]) {
                throw new RuntimeException('Cannot insert words. Possible deadlock? Database reported: ' . $e->getMessage(), 0, $e);
            }
            throw new UnknownException(sprintf(
                'Unknown exception with code "%s" occurred while fulltext indexing: "%s".',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }
    }

    /**
     * @param string[] $words
     *
     * @return array
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function findIdsByWords(array $words)
    {
        $partWords = self::getPartWords($words);

        $sql = '
			SELECT name, id
			FROM ' . $this->getTableName(self::WORD) . ' AS w
			WHERE name IN (' . implode(',', array_fill(0, count($partWords), '?')) . ')
		';

        try {
            $st = $this->pdo->prepare($sql);
            $st->execute(array_values($partWords));
        } catch (\PDOException $e) {
            if (1412 === (int)$e->errorInfo[1]) {
                throw new EmptyIndexException('Storage tables has been changed in the database. Is ' . __CLASS__ . '::erase() running in another process?', 0, $e);
            }
            throw new UnknownException(sprintf(
                'Unknown exception "%s" occurred while reading word dictionary: "%s".',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }

        $data = $st->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_COLUMN | \PDO::FETCH_UNIQUE) ?: [];

        $result = [];
        foreach ($partWords as $fullWord => $partWord) {
            if (isset($data[$partWords[$fullWord]])) {
                $result[$fullWord] = $data[$partWords[$fullWord]];
            }
        }

        return $result;
    }

    /**
     * @param array $words
     * @param array $wordIds
     * @param int   $internalId
     *
     * @throws UnknownException
     */
    public function insertFulltext(array $words, array $wordIds, $internalId)
    {
        $data = [];
        foreach ($words as $position => $word) {
            $expr        = $wordIds[$word] . ',' . $internalId . ',' . ((int)$position);
            $data[$expr] = $expr;
        }

        $sql = 'INSERT INTO ' . $this->getTableName(self::FULLTEXT_INDEX) . ' (word_id, toc_id, position) VALUES ( ' . implode('),(', $data) . ')';

        try {
            $this->pdo->exec($sql);
        } catch (\PDOException $e) {
            throw new UnknownException(sprintf(
                'Unknown exception with code "%s" occurred while fulltext indexing: "%s".',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }
    }


    /**
     * @param array    $words
     * @param int|null $instanceId
     *
     * @return array
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function findFulltextByWords(array $words, $instanceId = null)
    {
        $sql = '
			SELECT w.name AS word, t.external_id, t.instance_id, f.position
			FROM ' . $this->getTableName(self::FULLTEXT_INDEX) . ' AS f
			JOIN ' . $this->getTableName(self::WORD) . ' AS w ON w.id = f.word_id
			JOIN ' . $this->getTableName(self::TOC) . ' AS t ON t.id = f.toc_id
			WHERE w.name IN (' . implode(',', array_fill(0, count($words), '?')) . ')
		';

        $parameters = $words;
        if ($instanceId !== null) {
            $sql          .= ' AND t.instance_id = ?';
            $parameters[] = $instanceId;
        }

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($parameters);
        } catch (\PDOException $e) {
            if ($e->getCode() === '42S02') {
                throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
            }
            throw new UnknownException(sprintf(
                'Unknown exception "%s" occurred while fulltext searching: "%s".',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $data;
    }

    /**
     * @param string[] $words
     * @param int      $internalId
     * @param int      $type
     * @param string   $tableKey
     */
    public function insertKeywords(array $words, $internalId, $type, $tableKey)
    {
        $data = [];
        foreach ($words as $keyword) {// Ready for bulk insert
            $data[] = $this->pdo->quote($keyword) . ',' . $internalId . ',' . ((int)$type);
        }

        $sql = 'INSERT INTO ' . $this->getTableName($tableKey) . ' (keyword, toc_id, type) VALUES ( ' . implode('),(', $data) . ')';
        $this->pdo->exec($sql);
    }

    /**
     * @param string[] $words
     * @param int|null $instanceId
     *
     * @return array
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function findSingleKeywordIndex(array $words, $instanceId)
    {
        $usageSql = '
            SELECT COUNT(DISTINCT f.toc_id)
            FROM ' . $this->getTableName(self::FULLTEXT_INDEX) . ' AS f
            JOIN ' . $this->getTableName(self::WORD) . ' AS w ON w.id = f.word_id
            ' . (
            $instanceId !== null
                ? 'JOIN ' . $this->getTableName(self::TOC) . ' AS t ON t.id = f.toc_id AND t.instance_id = ' . (int)$instanceId . ' '
                : ''
            ) . ' WHERE k.keyword = w.name';

        $sql = '
			SELECT
				k.keyword,
				t.external_id,
				t.instance_id,
				k.type,
				(' . $usageSql . ') AS usage_num
			FROM ' . $this->getTableName(self::KEYWORD_INDEX) . ' AS k
			JOIN ' . $this->getTableName(self::TOC) . ' AS t ON t.id = k.toc_id
			WHERE k.keyword IN (' . implode(',', array_fill(0, count($words), '?')) . ')
		';

        $parameters = $words;
        if ($instanceId !== null) {
            $sql          .= ' AND t.instance_id = ?';
            $parameters[] = $instanceId;
        }

        try {
            $st = $this->pdo->prepare($sql);
            $st->execute($parameters);
        } catch (\PDOException $e) {
            if ($e->getCode() === '42S02') {
                throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
            }
            throw new UnknownException(sprintf(
                'Unknown exception "%s" occurred while single keywords searching: "%s".',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }

        $data = $st->fetchAll(\PDO::FETCH_ASSOC);

        return $data;
    }

    /**
     * @param string   $string
     * @param int|null $instanceId
     *
     * @return array
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function findMultipleKeywordIndex($string, $instanceId)
    {
        $sql = '
			SELECT t.external_id, t.instance_id, k.type
			FROM ' . $this->getTableName(self::KEYWORD_MULTIPLE_INDEX) . ' AS k
			JOIN ' . $this->getTableName(self::TOC) . ' AS t ON t.id = k.toc_id
			WHERE k.keyword LIKE ? ESCAPE \'=\'
		';

        $parameters = ['% ' . $this->escapeLike($string, '=') . ' %'];
        if ($instanceId !== null) {
            $sql          .= ' AND t.instance_id = ?';
            $parameters[] = $instanceId;
        }

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($parameters);
        } catch (\PDOException $e) {
            if ($e->getCode() === '42S02') {
                throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
            }
            throw new UnknownException(sprintf(
                'Unknown exception "%s" occurred while multiple keywords searching: "%s".',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }

        // TODO \PDO::FETCH_UNIQUE seems to be a hack for caller. Rewrite using INSERT IGNORE? @see \S2\Rose\Storage\KeywordIndexContent::add
        $data = $statement->fetchAll(\PDO::FETCH_COLUMN | \PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);

        return $data;
    }

    /**
     * @param ExternalId $externalId
     *
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function removeFromIndex(ExternalId $externalId)
    {
        $tocId = $this->selectInternalId($externalId);
        if ($tocId === null) {
            return;
        }

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
            throw new UnknownException(sprintf(
                'Unknown exception with code "%s" occurred while removing from index: "%s"',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }
    }

    /**
     * @param TocEntry   $entry
     * @param ExternalId $externalId
     *
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function addToToc(TocEntry $entry, ExternalId $externalId)
    {
        $sql = 'INSERT INTO ' . $this->getTableName(self::TOC) .
            ' (external_id, instance_id, title, description, added_at, url, hash)' .
            ' VALUES (:external_id, :instance_id, :title, :description, :added_at, :url, :hash)' .
            ' ON DUPLICATE KEY UPDATE title = :title, description = :description, added_at = :added_at, url = :url, hash = :hash';

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                'external_id' => $externalId->getId(),
                'instance_id' => (int)$externalId->getInstanceId(),
                'title'       => $entry->getTitle(),
                'description' => $entry->getDescription(),
                'added_at'    => $entry->getFormattedDate(),
                'url'         => $entry->getUrl(),
                'hash'        => $entry->getHash(),
            ]);
        } catch (\PDOException $e) {
            if ($e->getCode() === '42S02') {
                throw new EmptyIndexException(
                    'There are missing storage tables in the database. Is ' . __CLASS__ . '::erase() running in another process?',
                    0,
                    $e
                );
            }
            throw new UnknownException(sprintf(
                'Unknown exception with code "%s" occurred while adding to TOC: "%s".',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }
    }

    /**
     * @param array $criteria
     *
     * @return array
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function getTocEntries(array $criteria = [])
    {
        try {
            if (isset($criteria['title'])) {
                // TODO remove
                $sql = '
					SELECT *
					FROM ' . $this->getTableName(self::TOC) . ' AS t
					WHERE t.title LIKE ? ESCAPE \'=\'
				';

                $statement = $this->pdo->prepare($sql);
                $statement->execute(['%' . $this->escapeLike($criteria['title'], '=') . '%']);
            } elseif (isset($criteria['ids'])) {
                if (!($criteria['ids'] instanceof ExternalIdCollection)) {
                    throw new InvalidArgumentException('Ids must be an ExternalIdCollection.');
                }
                $ids = $criteria['ids']->toArray();
                if (count($ids) === 0) {
                    return [];
                }
                $sql = '
					SELECT *
					FROM ' . $this->getTableName(self::TOC) . ' AS t
					WHERE (t.external_id, t.instance_id) IN (' . implode(',', array_fill(0, count($ids), '(?, ?)')) . ')';

                $statement = $this->pdo->prepare($sql);
                $params    = [];
                foreach ($ids as $id) {
                    $params[] = $id->getId();
                    $params[] = (int)$id->getInstanceId();
                }
                $statement->execute($params);
            } else {
                throw new InvalidArgumentException('Criteria must contain title or ids conditions.');
            }
        } catch (\PDOException $e) {
            if ($e->getCode() === '42S02') {
                throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
            }
            throw new UnknownException(sprintf(
                'Unknown exception with code "%s" occurred while reading TOC: "%s".',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }

        return $statement->fetchAll();
    }

    /**
     * @param int|null $instanceId
     *
     * @return int
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function getTocSize($instanceId)
    {
        $sql = 'SELECT count(*) FROM ' . $this->getTableName(self::TOC);
        if ($instanceId !== null) {
            $sql .= ' WHERE instance_id = ?';
        }

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($instanceId !== null ? [$instanceId] : []);
        } catch (\PDOException $e) {
            if ($e->getCode() === '42S02') {
                throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
            }
            throw new UnknownException(sprintf(
                'Unknown exception with code "%s" occurred while obtaining TOC size: "%s".',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }

        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        return $result;
    }

    /**
     * @param ExternalId $externalId
     *
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function removeFromToc(ExternalId $externalId)
    {
        $sql = '
			DELETE FROM ' . $this->getTableName(self::TOC) . '
			WHERE external_id = ? AND instance_id = ?
		';

        try {
            $st = $this->pdo->prepare($sql);
            $st->execute([$externalId->getId(), (int)$externalId->getInstanceId()]);
        } catch (\PDOException $e) {
            if ($e->getCode() === '42S02') {
                throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
            }
            throw new UnknownException(sprintf(
                'Unknown exception with code "%s" occurred while removing from TOC: "%s".',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }
    }

    /**
     * @param ExternalId $externalId
     *
     * @return int|null
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function selectInternalId(ExternalId $externalId)
    {
        $sql = 'SELECT id FROM ' . $this->getTableName(self::TOC) . ' WHERE external_id = ? AND instance_id = ?';

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([$externalId->getId(), (int)$externalId->getInstanceId()]);

        } catch (\PDOException $e) {
            if ($e->getCode() === '42S02') {
                throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
            }
            throw new UnknownException(sprintf(
                'Unknown exception with code "%s" occurred while removing from index: "%s"',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }

        $internalId = $statement->fetch(\PDO::FETCH_COLUMN);

        return $internalId;
    }

    /**
     * @throws UnknownException
     */
    public function startTransaction()
    {
        try {
            $this->pdo->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
            $this->pdo->beginTransaction();
        } catch (\PDOException $e) {
            throw new UnknownException(sprintf(
                'Unknown exception "%s" occurred while starting transaction: "%s".',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }
    }

    /**
     * @throws UnknownException
     */
    public function commitTransaction()
    {
        try {
            $this->pdo->commit();
        } catch (\PDOException $e) {
            throw new UnknownException(sprintf(
                'Unknown exception "%s" occurred while committing transaction: "%s".',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }
    }

    /**
     * @throws UnknownException
     */
    public function rollbackTransaction()
    {
        try {
            $this->pdo->rollBack();
        } catch (\PDOException $e) {
            throw new UnknownException(sprintf(
                'Unknown exception "%s" occurred while transaction rollback: "%s".',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }
    }

    /**
     * @param string $key
     *
     * @return string
     */
    private function getTableName($key)
    {
        if (!isset($this->options[$key])) {
            throw new InvalidArgumentException(sprintf('Unknown table "%s"', $key));
        }

        return $this->prefix . $this->options[$key];
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
     * Converts array of (long) words to array of word parts no longer than 191 chars.
     *
     * @param string[] $words
     *
     * @return string[]
     */
    public static function getPartWords(array $words)
    {
        $partWords = [];
        foreach ($words as $fullWord) {
            $partWords[$fullWord] = mb_substr($fullWord, 0, self::UTF8MB4_KEYLEN);
        }

        return $partWords;
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
			instance_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
			title VARCHAR(255) NOT NULL DEFAULT "",
			description TEXT NOT NULL,
			added_at DATETIME NULL,
			url TEXT NOT NULL,
			hash VARCHAR(80) NOT NULL DEFAULT "",
			PRIMARY KEY (`id`),
			UNIQUE KEY (instance_id, external_id(' . $keyLen . '))
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
			UNIQUE KEY (name(' . $keyLen . '))
		) ENGINE=InnoDB CHARACTER SET ' . $charset . ' COLLATE ' . $charset . '_bin');

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
