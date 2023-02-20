<?php /** @noinspection OneTimeUseVariablesInspection */
/** @noinspection PhpUnnecessaryLocalVariableInspection */
/** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */
/** @noinspection PhpComposerExtensionStubsInspection */

/**
 * @copyright 2020-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage\Database;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\ExternalIdCollection;
use S2\Rose\Entity\Metadata\SnippetSource;
use S2\Rose\Entity\TocEntry;
use S2\Rose\Exception\InvalidArgumentException;
use S2\Rose\Exception\RuntimeException;
use S2\Rose\Exception\UnknownException;
use S2\Rose\Storage\Dto\SnippetQuery;
use S2\Rose\Storage\Exception\EmptyIndexException;
use S2\Rose\Storage\Exception\InvalidEnvironmentException;

class MysqlRepository
{
    public const TOC                    = 'toc';
    public const WORD                   = 'word';
    public const METADATA               = 'metadata';
    public const SNIPPET                = 'snippet';
    public const FULLTEXT_INDEX         = 'fulltext_index';
    public const KEYWORD_INDEX          = 'keyword_index';
    public const KEYWORD_MULTIPLE_INDEX = 'keyword_multiple_index';

    private const KEYLEN              = 255;
    private const UTF8MB4_KEYLEN      = 191;
    private const DEFAULT_TABLE_NAMES = [
        self::TOC                    => 'toc',
        self::WORD                   => 'word',
        self::METADATA               => 'metadata',
        self::SNIPPET                => 'snippet',
        self::FULLTEXT_INDEX         => 'fulltext_index',
        self::KEYWORD_INDEX          => 'keyword_index',
        self::KEYWORD_MULTIPLE_INDEX => 'keyword_multiple_index',
    ];

    protected \PDO $pdo;
    protected string $prefix;
    protected array $options;

    public function __construct(\PDO $pdo, string $prefix = '', array $options = [])
    {
        $this->pdo     = $pdo;
        $this->prefix  = $prefix;
        $this->options = array_merge(self::DEFAULT_TABLE_NAMES, $options);
    }

    /**
     * @throws InvalidEnvironmentException
     * @throws UnknownException
     */
    public function erase(): void
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
    public function insertWords(array $words): void
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
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function findIdsByWords(array $words): array
    {
        $partWords = self::getPartWords($words);

        $sql = '
			SELECT name, id
			FROM ' . $this->getTableName(self::WORD) . ' AS w
			WHERE name IN (' . implode(',', array_fill(0, \count($partWords), '?')) . ')
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
            if (isset($data[$partWord])) {
                $result[$fullWord] = $data[$partWord];
            }
        }

        return $result;
    }

    /**
     * @throws UnknownException
     */
    public function insertFulltext(array $words, array $wordIds, int $internalId): void
    {
        $data = [];
        foreach ($words as $position => $word) {
            $key          = $wordIds[$word];
            $data[$key][] = (int)$position;
        }

        if (\count($data) === 0) {
            return;
        }
        $sqlParts = '';
        foreach ($data as $wordId => $positions) {
            $sqlParts .= ($sqlParts !== '' ? ',' : '') . '(' . $wordId . ',' . $internalId . ',\'' . implode(',', $positions) . '\')';
        }

        $sql = 'INSERT INTO ' . $this->getTableName(self::FULLTEXT_INDEX)
            . ' (word_id, toc_id, positions) VALUES ' . $sqlParts;

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
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function findFulltextByWords(array $words, int $instanceId = null): array
    {
        $sql = '
			SELECT w.name AS word, t.external_id, t.instance_id, f.positions, COALESCE(m.word_count, 0) AS word_count
			FROM ' . $this->getTableName(self::FULLTEXT_INDEX) . ' AS f
			JOIN ' . $this->getTableName(self::WORD) . ' AS w ON w.id = f.word_id
			JOIN ' . $this->getTableName(self::TOC) . ' AS t ON t.id = f.toc_id
			LEFT JOIN ' . $this->getTableName(self::METADATA) . ' AS m ON t.id = m.toc_id
			WHERE w.name IN (' . implode(',', array_fill(0, \count($words), '?')) . ')
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
            if ($e->getCode() === '42S22') { // e.g. SQLSTATE[42S22]: Column not found: 1054 Unknown column 'f.positions' in 'field list'
                throw new EmptyIndexException('There are no storage tables in the database. Call ' . __CLASS__ . '::erase() first.', 0, $e);
            }
            throw new UnknownException(sprintf(
                'Unknown exception "%s" occurred while fulltext searching: "%s".',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($data as &$row) {
            $row['positions'] = explode(',', $row['positions']);
        }
        unset($row);

        return $data;
    }

    /**
     * @param string[] $words
     */
    public function insertKeywords(array $words, int $internalId, int $type, string $tableKey): void
    {
        $data = [];
        foreach ($words as $keyword) {// Ready for bulk insert
            $data[] = $this->pdo->quote($keyword) . ',' . $internalId . ',' . $type;
        }

        $sql = 'INSERT INTO ' . $this->getTableName($tableKey) . ' (keyword, toc_id, type) VALUES ( ' . implode('),(', $data) . ')';
        $this->pdo->exec($sql);
    }

    /**
     * @throws UnknownException
     */
    public function insertMetadata(int $internalId, int $wordCount, string $imagesJson): void
    {
        $st = $this->pdo->prepare('INSERT INTO ' . $this->getTableName(self::METADATA) . ' (toc_id, word_count, images) VALUES (?, ?, ?)');
        try {
            $st->execute([$internalId, $wordCount, $imagesJson]);
        } catch (\PDOException $e) {
            throw new UnknownException(sprintf(
                'Unknown exception "%s" occurred while inserting metadata: "%s".',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }
    }

    public function insertSnippets(int $internalId, SnippetSource ...$snippetInfo): void
    {
        $data = array_map(fn(SnippetSource $s) => $s->getMinPosition() . ','
            . $s->getMaxPosition() . ','
            . $this->pdo->quote($s->getText()) . ','
            . $internalId, $snippetInfo);

        $sql = 'INSERT INTO ' . $this->getTableName(self::SNIPPET)
            . ' (min_word_pos, max_word_pos, snippet, toc_id) VALUES ( ' . implode('),(', $data) . ')';
        try {
            $this->pdo->exec($sql);
        } catch (\PDOException $e) {
            throw new UnknownException(sprintf(
                'Unknown exception "%s" occurred while inserting snippets: "%s".',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }
    }

    /**
     * @param string[] $words
     *
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function findSingleKeywordIndex(array $words, ?int $instanceId): array
    {
        $usageSql = '
            SELECT COUNT(DISTINCT f.toc_id)
            FROM ' . $this->getTableName(self::FULLTEXT_INDEX) . ' AS f
            JOIN ' . $this->getTableName(self::WORD) . ' AS w ON w.id = f.word_id
            ' . (
            $instanceId !== null
                ? 'JOIN ' . $this->getTableName(self::TOC) . ' AS t ON t.id = f.toc_id AND t.instance_id = ' . $instanceId . ' '
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
			WHERE k.keyword IN (' . implode(',', array_fill(0, \count($words), '?')) . ')
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
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function findMultipleKeywordIndex(string $string, ?int $instanceId): array
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

    public function getSnippets(SnippetQuery $snippetQuery): array
    {
        $internalIds   = $this->selectInternalIds(...$snippetQuery->getExternalIds());
        $orWhere       = [];
        $fallbackWhere = [];
        $snippetQuery->iterate(function (ExternalId $externalId, ?array $positions) use ($internalIds, &$orWhere, &$fallbackWhere) {
            // Add a first sentence to snippets if there are no matched snippets.
            $fallbackWhere[] = 's.toc_id = ' . $internalIds[$externalId->toString()];
            if (\count($positions ?? []) === 0) {
                // Seems like fallback snippets must be fetched here. But fulltext index can contain
                // some "fantom" entries with positions out of scope (e.g. keywords).
                // In that case there will be no snippets returned. So now the fallback snippets are fetched anyway.
                return;
            }

            $orWhere[] = 's.toc_id = ' . $internalIds[$externalId->toString()] . ' AND ('
                . implode(' OR ', array_map(
                    static fn(int $pos) => sprintf('s.min_word_pos <= %1$s AND s.max_word_pos >= %1$s', $pos),
                    $positions
                ))
                . ')';
        });

        if (\count($orWhere) === 0) {
            return [];
        }

        $sql = '(SELECT s.*
			FROM ' . $this->getTableName(self::SNIPPET) . ' AS s
			WHERE ' . implode(' OR ', $orWhere) . '
			)';

        foreach ($fallbackWhere as $fallbackWhereItem) {
            $sql .= ' UNION (
                SELECT s.*
                FROM ' . $this->getTableName(self::SNIPPET) . ' AS s
                WHERE ' . $fallbackWhereItem . '
                ORDER BY s.max_word_pos
                LIMIT 2
			)';
        }

        $sql .= ' ORDER BY toc_id, max_word_pos';

        $statement = $this->pdo->query($sql);

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $externalIds = array_flip($internalIds);
        foreach ($data as &$row) {
            $row['externalId'] = ExternalId::fromString($externalIds[$row['toc_id']]);
        }

        return $data;
    }

    /**
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function removeFromIndex(ExternalId $externalId): void
    {
        $tocId = $this->selectInternalId($externalId);
        if ($tocId === null) {
            return;
        }

        try {
            $st = $this->pdo->prepare("DELETE FROM {$this->getTableName(self::FULLTEXT_INDEX)} WHERE toc_id = ?");
            $st->execute([$tocId]);

            $st = $this->pdo->prepare("DELETE FROM {$this->getTableName(self::KEYWORD_INDEX)} WHERE toc_id = ?");
            $st->execute([$tocId]);

            $st = $this->pdo->prepare("DELETE FROM {$this->getTableName(self::KEYWORD_MULTIPLE_INDEX)} WHERE toc_id = ?");
            $st->execute([$tocId]);

            $st = $this->pdo->prepare("DELETE FROM {$this->getTableName(self::METADATA)} WHERE toc_id = ?");
            $st->execute([$tocId]);

            $st = $this->pdo->prepare("DELETE FROM {$this->getTableName(self::SNIPPET)} WHERE toc_id = ?");
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
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function addToToc(TocEntry $entry, ExternalId $externalId): void
    {
        $sql = 'INSERT INTO ' . $this->getTableName(self::TOC) .
            ' (external_id, instance_id, title, description, added_at, url, relevance_ratio, hash)' .
            ' VALUES (:external_id, :instance_id, :title, :description, :added_at, :url, :relevance_ratio, :hash)' .
            ' ON DUPLICATE KEY UPDATE title = :title, description = :description, added_at = :added_at, url = :url, relevance_ratio = :relevance_ratio, hash = :hash';

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                'external_id'     => $externalId->getId(),
                'instance_id'     => (int)$externalId->getInstanceId(),
                'title'           => $entry->getTitle(),
                'description'     => $entry->getDescription(),
                'added_at'        => $entry->getFormattedDate(),
                'url'             => $entry->getUrl(),
                'relevance_ratio' => $entry->getRelevanceRatio(),
                'hash'            => $entry->getHash(),
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
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function getTocEntries(array $criteria = []): array
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
                if (\count($ids) === 0) {
                    return [];
                }
                $sql = '
					SELECT t.*, m.*
					FROM ' . $this->getTableName(self::TOC) . ' AS t
					LEFT JOIN ' . $this->getTableName(self::METADATA) . ' AS m ON m.toc_id = t.id
					WHERE (t.external_id, t.instance_id) IN (' . implode(',', array_fill(0, \count($ids), '(?, ?)')) . ')';

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

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function getTocSize(?int $instanceId): int
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
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function removeFromToc(ExternalId $externalId): void
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

    public function getSimilar(ExternalId $externalId, ?int $instanceId = null, int $minCommonWords = 4, int $limit = 10): array
    {
        $tocTable      = $this->getTableName(self::TOC);
        $wordTable     = $this->getTableName(self::WORD);
        $snippetTable  = $this->getTableName(self::SNIPPET);
        $fulltextTable = $this->getTableName(self::FULLTEXT_INDEX);
        $metadataTable = $this->getTableName(self::METADATA);

        $where = $instanceId !== null ? 'WHERE t.instance_id = ' . $instanceId : '';

        // Sorry for comments in Russian. Anyway I'm the one who will support it :)
        $sql = "
SELECT
    relevance_info.*, -- информация из подзапроса
    m.images, -- добавляем к ней информацию о картинках
    t.*, -- добавляем к ней оглавление
    -- и первые 2 предложения из текста
    (SELECT snippet FROM {$snippetTable} AS sn WHERE sn.toc_id = t.id ORDER BY sn.max_word_pos LIMIT 1) AS snippet,
    (SELECT snippet FROM {$snippetTable} AS sn WHERE sn.toc_id = t.id ORDER BY sn.max_word_pos LIMIT 1 OFFSET 1) AS snippet2
FROM (
    SELECT -- Перебираем все возможные заметки и вычисляем релевантность каждой для подбора рекомендаций
        i.toc_id,
        sum(
            original_repeat + -- доп. 1 за каждый повтор слова в оригинальной заметке
            exp( - abn/30.0 ) -- понижение веса у распространенных слов
                * (1 + length(positions) - length(replace(positions, ',', ''))) -- повышение при повторе в рекомендуемой заметке, конструкция тождественна count(explode(',', positions))
        ) * pow(m.word_count, -0.5) AS relevance, -- тут нормировка на корень из размера рекомендуемой заметки. Не знаю, почему именно корень, но так работает хорошо.
        m.word_count,
        GROUP_CONCAT(concat(w.name, ' ',  round(original_repeat + exp( -pow( (abn/30.0),1) )/1.0, 3)   )) AS names -- TODO remove debug
    FROM {$fulltextTable} AS i
        JOIN {$wordTable} AS w ON i.word_id = w.id -- TODO remove debug
        JOIN {$metadataTable} AS m FORCE INDEX FOR JOIN(PRIMARY) ON m.toc_id = i.toc_id
    JOIN (
        SELECT -- достаем информацию по словам из оригинальной заметки
            word_id,
            toc_id,
            (SELECT count(*) FROM {$fulltextTable} WHERE word_id = x.word_id) AS abn, -- распространенность текущего слова по всем заметкам
            length(positions) - length(replace(positions, ',', '')) AS original_repeat -- сколько раз слово повторяется в оригинальной заметке. Выше используется как доп. важность
        FROM {$fulltextTable} AS x FORCE INDEX FOR JOIN(toc_id)
        JOIN {$tocTable} AS t ON t.id = x.toc_id
        WHERE t.external_id = :external_id AND t.instance_id = :instance_id
            AND length(positions) - length(replace(positions, ',', '')) < 200 -- отсекаем слишком частые слова. Хотя 200 слишком завышенный порог, чтобы на что-то менять
            -- AND length(positions) - length(replace(positions, ',', '')) >= 1 -- слово должно повторяться в оригинальной заметке минимум 2 раза  -- вместо этого придумал original_repeat
        HAVING abn < 100 -- если слово встречается более чем в 100 заметках, выкидываем его, так как слишком частое. Помогает с производительностью
    ) AS original_info ON original_info.word_id = i.word_id AND original_info.toc_id <> i.toc_id
    GROUP BY 1
    HAVING count(*) >= :min_common_words -- количество общих слов, иначе отбрасываем // вот это тоже добавить в сортировку релевантности
) AS relevance_info
JOIN {$tocTable} AS t FORCE INDEX FOR JOIN(PRIMARY) on t.id = relevance_info.toc_id
JOIN {$metadataTable} AS m FORCE INDEX FOR JOIN(PRIMARY) on m.toc_id = t.id
{$where}
ORDER BY relevance DESC
LIMIT :limit";
        $st  = $this->pdo->prepare($sql);
        $st->bindValue('external_id', $externalId->getId(), \PDO::PARAM_STR);
        $st->bindValue('instance_id', (int)$externalId->getInstanceId(), \PDO::PARAM_INT);
        $st->bindValue('limit', $limit, \PDO::PARAM_INT);
        $st->bindValue('min_common_words', $minCommonWords, \PDO::PARAM_INT);
        $st->execute();
        $recommendations = $st->fetchAll(\PDO::FETCH_ASSOC);

        return $recommendations;
    }

    /**
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function selectInternalId(ExternalId $externalId): ?int
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

    public function selectInternalIds(ExternalId ...$externalIds): array
    {
        $sql = '
            SELECT id, external_id, instance_id
            FROM ' . $this->getTableName(self::TOC) . ' AS t
            WHERE (t.external_id, t.instance_id) IN (' . implode(',', array_fill(0, \count($externalIds), '(?, ?)')) . ')';

        $params = [];
        foreach ($externalIds as $id) {
            $params[] = $id->getId();
            $params[] = (int)$id->getInstanceId();
        }
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($params);

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

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($data as $row) {
            $result[$this->getExternalIdFromRow($row)->toString()] = $row['id'];
        }

        return $result;
    }

    /**
     * @throws UnknownException
     */
    public function startTransaction(): void
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
    public function commitTransaction(): void
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
    public function rollbackTransaction(): void
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
     * @throws EmptyIndexException
     */
    public function getIndexStat(): array
    {
        $tableNames = array_map(function ($s) {
            return $this->pdo->quote($this->getTableName($s));
        }, array_keys(self::DEFAULT_TABLE_NAMES));

        $sql           = 'SHOW TABLE STATUS WHERE name IN (' . implode(',', $tableNames) . ')';
        $tableStatuses = $this->pdo->query($sql)->fetchAll();

        if (\count($tableStatuses) !== \count($tableNames)) {
            throw new EmptyIndexException('Some storage tables are missed in the database. Call ' . __CLASS__ . '::erase() first.');
        }

        $indexSize = 0;
        $indexRows = 0;
        foreach ($tableStatuses as $tableStatus) {
            $indexSize += $tableStatus['Data_length'] + $tableStatus['Index_length'];
            $indexRows += $tableStatus['Rows'];
        }

        return [
            'bytes' => $indexSize,
            'rows'  => $indexRows,
        ];
    }

    private function getTableName(string $key): string
    {
        if (!isset($this->options[$key])) {
            throw new InvalidArgumentException(sprintf('Unknown table "%s"', $key));
        }

        return $this->prefix . $this->options[$key];
    }

    /**
     * @see http://stackoverflow.com/questions/3683746/escaping-mysql-wild-cards
     */
    private function escapeLike(string $s, string $e): string
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
    public static function getPartWords(array $words): array
    {
        $partWords = [];
        foreach ($words as $fullWord) {
            $partWords[$fullWord] = mb_substr($fullWord, 0, self::UTF8MB4_KEYLEN);
        }

        return $partWords;
    }

    private function dropAndCreateTables(string $charset, int $keyLen): void
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
			relevance_ratio DECIMAL(4,3) NOT NULL,
			hash VARCHAR(80) NOT NULL DEFAULT "",
			PRIMARY KEY (`id`),
			UNIQUE KEY (instance_id, external_id(' . $keyLen . '))
		) ENGINE=InnoDB CHARACTER SET ' . $charset);

        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::METADATA) . ';');
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::METADATA) . ' (
			toc_id INT(11) UNSIGNED NOT NULL,
			word_count INT(11) UNSIGNED NOT NULL,
			images JSON NOT NULL,
			PRIMARY KEY (toc_id)
		) ENGINE=InnoDB CHARACTER SET ' . $charset);

        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::SNIPPET) . ';');
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::SNIPPET) . ' (
			toc_id INT(11) UNSIGNED NOT NULL,
			max_word_pos INT(11) UNSIGNED NOT NULL,
			min_word_pos INT(11) UNSIGNED NOT NULL,
			snippet LONGTEXT NOT NULL,
			PRIMARY KEY (toc_id, max_word_pos)
		) ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4 ENGINE=InnoDB CHARACTER SET ' . $charset);

        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::FULLTEXT_INDEX) . ';');
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::FULLTEXT_INDEX) . ' (
			word_id INT(11) UNSIGNED NOT NULL,
			toc_id INT(11) UNSIGNED NOT NULL,
			positions LONGTEXT NOT NULL,
			PRIMARY KEY (word_id, toc_id),
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

    private function getExternalIdFromRow(array $row): ExternalId
    {
        return new ExternalId($row['external_id'], $row['instance_id'] > 0 ? $row['instance_id'] : null);
    }
}
