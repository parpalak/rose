<?php /** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection PhpUnnecessaryLocalVariableInspection */
/** @noinspection SqlDialectInspection */
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

declare(strict_types=1);

namespace S2\Rose\Storage\Database;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\TocEntry;
use S2\Rose\Exception\RuntimeException;
use S2\Rose\Exception\UnknownException;
use S2\Rose\Storage\Dto\SnippetQuery;
use S2\Rose\Storage\Exception\EmptyIndexException;
use S2\Rose\Storage\Exception\InvalidEnvironmentException;

class SqliteRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     */
    public function erase(): void
    {
        try {
            $this->dropAndCreateTables();
        } catch (\PDOException $e) {
            if ($this->isLockWaitingException($e)) {
                throw new RuntimeException('Cannot drop and create tables. Possible deadlock? Database reported: ' . $e->getMessage(), 0, $e);
            }
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
     * {@inheritdoc}
     *
     * @throws RuntimeException
     */
    public function insertWords(array $words): void
    {
        $partWords = static::prepareWords($words);

        $sql = 'INSERT INTO ' . $this->getTableName(AbstractRepository::WORD) . " (name) VALUES (" . implode(
                "),(",
                array_map(fn($x) => $this->pdo->quote($x), $partWords)
            ) . ") ON CONFLICT DO NOTHING";

        try {
            $this->pdo->exec($sql);
        } catch (\PDOException $e) {
            if ($this->isLockWaitingException($e)) {
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
     * {@inheritdoc}
     *
     * @throws EmptyIndexException
     * @throws UnknownException
     * @throws RuntimeException
     */
    public function addToToc(TocEntry $entry, ExternalId $externalId): void
    {
        $sql = 'INSERT INTO ' . $this->getTableName(self::TOC) .
            ' (external_id, instance_id, title, description, added_at, timezone, url, relevance_ratio, hash)' .
            ' VALUES (:external_id, :instance_id, :title, :description, :added_at, :timezone, :url, :relevance_ratio, :hash)' .
            ' ON CONFLICT (external_id, instance_id) DO UPDATE' .
            ' SET title = :title, description = :description, added_at = :added_at, timezone = :timezone, url = :url, relevance_ratio = :relevance_ratio, hash = :hash';

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                'external_id'     => $externalId->getId(),
                'instance_id'     => (int)$externalId->getInstanceId(),
                'title'           => $entry->getTitle(),
                'description'     => $entry->getDescription(),
                'added_at'        => $entry->getFormattedDate(),
                'timezone'        => $entry->getTimeZone(),
                'url'             => $entry->getUrl(),
                'relevance_ratio' => $entry->getRelevanceRatio(),
                'hash'            => $entry->getHash(),
            ]);
        } catch (\PDOException $e) {
            if ($this->isLockWaitingException($e)) {
                // Possible for SQLite
                throw new RuntimeException(
                    'Cannot insert new items. Possible deadlock? Database reported: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
            if ($this->isUnknownTableException($e)) {
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
     * {@inheritdoc}
     */
    public function getSimilar(ExternalId $externalId, ?int $instanceId = null, int $minCommonWords = 4, int $limit = 10): array
    {
        throw new \LogicException('Not implemented');
    }


    /**
     * {@inheritdoc}
     *
     * @throws EmptyIndexException
     */
    public function getIndexStat(): array
    {
        $tableNames = array_map(fn($s) => $this->pdo->quote($this->getTableName($s)), array_keys(self::DEFAULT_TABLE_NAMES));

        $sql = 'SELECT name, pgsize FROM dbstat WHERE name IN  (' . implode(',', $tableNames) . ');';

        $tableStatuses = $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        if (\count($tableStatuses) !== \count($tableNames)) {
            throw new EmptyIndexException(sprintf(
                'Some storage tables are missed in the database. Call %s::erase() first.',
                PdoStorage::class
            ));
        }

        $indexSize = 0;
        $indexRows = 0;
        foreach ($tableStatuses as $tableStatus) {
            $indexSize += $tableStatus['pgsize'];
            $indexRows += $this->pdo->query('SELECT count(*) FROM ' . $tableStatus['name'])->fetchColumn();
        }

        return [
            'bytes' => $indexSize,
            'rows'  => $indexRows,
        ];
    }

    /**
     * {@inheritdoc}
     */
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

        $sql = 'SELECT s.*
			FROM ' . $this->getTableName(self::SNIPPET) . ' AS s
			WHERE ' . implode(' OR ', $orWhere) . '
			';

        foreach ($fallbackWhere as $fallbackWhereItem) {
            $sql .= ' UNION SELECT * FROM (
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
     * {@inheritdoc}
     */
    public function startTransaction(): void
    {
        try {
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
     * {@inheritdoc}
     */
    protected function isUnknownTableException(\PDOException $e): bool
    {
        return 1 === $e->errorInfo[1] && 'HY000' === $e->errorInfo[0] && strpos($e->getMessage(), 'no such table') !== false; // SQLSTATE[HY000]: General error: 1 no such table: test_keyword_multiple_index
    }

    /**
     * {@inheritdoc}
     */
    protected function isLockWaitingException(\PDOException $e): bool
    {
        return 5 === $e->errorInfo[1] && 'HY000' === $e->errorInfo[0] && strpos($e->getMessage(), 'database is locked') !== false; // SQLSTATE[HY000]: General error: 5 database is locked
    }

    protected function isUnknownColumnException(\PDOException $e): bool
    {
        return 1 === $e->errorInfo[1] && 'HY000' === $e->errorInfo[0] && strpos($e->getMessage(), 'no such column') !== false;
    }

    private function dropAndCreateTables(): void
    {
        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::TOC) . ';');
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::TOC) . ' (
            id INTEGER PRIMARY KEY,
            external_id VARCHAR(255) NOT NULL,
            instance_id INTEGER NOT NULL DEFAULT 0,
            title VARCHAR(255) NOT NULL DEFAULT \'\',
            description TEXT NOT NULL,
            added_at TIMESTAMP WITHOUT TIME ZONE NULL,
            timezone VARCHAR(64) NULL,
            url TEXT NOT NULL,
            relevance_ratio DECIMAL(4,3) NOT NULL,
            hash VARCHAR(80) NOT NULL DEFAULT \'\',
            UNIQUE (instance_id, external_id)
		)');

        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::METADATA) . ';');
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::METADATA) . ' (
			toc_id INTEGER PRIMARY KEY,
			word_count INTEGER NOT NULL,
			images JSON NOT NULL
		)');

        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::SNIPPET) . ';');
        // TODO compression?
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::SNIPPET) . ' (
            toc_id INTEGER NOT NULL,
            max_word_pos INTEGER NOT NULL,
            min_word_pos INTEGER NOT NULL,
            format_id INTEGER NOT NULL,
            snippet TEXT NOT NULL,
            PRIMARY KEY (toc_id, max_word_pos)
		)');

        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::FULLTEXT_INDEX) . ';');
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::FULLTEXT_INDEX) . ' (
            word_id INTEGER NOT NULL,
            toc_id INTEGER NOT NULL,
            positions TEXT NOT NULL,
            PRIMARY KEY (word_id, toc_id)
		)');
        $this->pdo->exec(sprintf(
            'CREATE INDEX idx_%1$s_toc_id ON %1$s (toc_id);',
            $this->getTableName(self::FULLTEXT_INDEX)
        ));

        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::WORD) . ';');
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::WORD) . ' (
            id INTEGER PRIMARY KEY,
            name VARCHAR(255) NOT NULL DEFAULT \'\',
            UNIQUE (name)
		)');

        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::KEYWORD_INDEX) . ';');
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::KEYWORD_INDEX) . ' (
            keyword VARCHAR(255) NOT NULL,
            toc_id INTEGER NOT NULL,
            type INTEGER NOT NULL
		)');
        $this->pdo->exec(sprintf(
            'CREATE INDEX idx_%1$s_toc_id ON %1$s (toc_id);',
            $this->getTableName(self::KEYWORD_INDEX)
        ));
        $this->pdo->exec(sprintf(
            'CREATE INDEX idx_%1$s_keyword ON %1$s (keyword);',
            $this->getTableName(self::KEYWORD_INDEX)
        ));

        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::KEYWORD_MULTIPLE_INDEX) . ';');
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::KEYWORD_MULTIPLE_INDEX) . ' (
            keyword VARCHAR(255) NOT NULL,
            toc_id INTEGER NOT NULL,
            type INTEGER NOT NULL
		)');
        $this->pdo->exec(sprintf(
            'CREATE INDEX idx_%1$s_toc_id ON %1$s (toc_id);',
            $this->getTableName(self::KEYWORD_MULTIPLE_INDEX)
        ));
        $this->pdo->exec(sprintf(
            'CREATE INDEX idx_%1$s_keyword ON %1$s (keyword);',
            $this->getTableName(self::KEYWORD_MULTIPLE_INDEX)
        ));
    }
}
