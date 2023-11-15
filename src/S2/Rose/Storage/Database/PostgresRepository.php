<?php /** @noinspection PhpUnnecessaryLocalVariableInspection */
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
use S2\Rose\Storage\Exception\EmptyIndexException;
use S2\Rose\Storage\Exception\InvalidEnvironmentException;

class PostgresRepository extends AbstractRepository
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
        STRING_AGG(concat(w.name, ' ',  round(original_repeat + exp( -pow( (abn/30.0),1) )/1.0, 3)   ), ', ') AS names -- TODO remove debug
    FROM {$fulltextTable} AS i
        JOIN {$wordTable} AS w ON i.word_id = w.id -- TODO remove debug
        JOIN {$metadataTable} AS m ON m.toc_id = i.toc_id
    JOIN (
        SELECT -- достаем информацию по словам из оригинальной заметки
            word_id,
            toc_id,
            abn,
            length(positions) - length(replace(positions, ',', '')) AS original_repeat -- сколько раз слово повторяется в оригинальной заметке. Выше используется как доп. важность
        FROM {$fulltextTable} AS x
        JOIN {$tocTable} AS t ON t.id = x.toc_id
        CROSS JOIN LATERAL (SELECT count(*) AS abn FROM {$fulltextTable} WHERE word_id = x.word_id) AS fulltext2 -- распространенность текущего слова по всем заметкам
        WHERE t.external_id = :external_id AND t.instance_id = :instance_id
            AND length(positions) - length(replace(positions, ',', '')) < 200 -- отсекаем слишком частые слова. Хотя 200 слишком завышенный порог, чтобы на что-то менять
            -- AND length(positions) - length(replace(positions, ',', '')) >= 1 -- слово должно повторяться в оригинальной заметке минимум 2 раза  -- вместо этого придумал original_repeat
            AND abn < 100 -- если слово встречается более чем в 100 заметках, выкидываем его, так как слишком частое. Помогает с производительностью
    ) AS original_info ON original_info.word_id = i.word_id AND original_info.toc_id <> i.toc_id
    GROUP BY i.toc_id, m.word_count
    HAVING count(*) >= :min_common_words -- количество общих слов, иначе отбрасываем // вот это тоже добавить в сортировку релевантности
) AS relevance_info
JOIN {$tocTable} AS t ON t.id = relevance_info.toc_id
JOIN {$metadataTable} AS m ON m.toc_id = t.id
{$where}
ORDER BY relevance DESC
LIMIT :limit";

        try {
            $st  = $this->pdo->prepare($sql);
            $st->bindValue('external_id', $externalId->getId(), \PDO::PARAM_STR);
            $st->bindValue('instance_id', (int)$externalId->getInstanceId(), \PDO::PARAM_INT);
            $st->bindValue('limit', $limit, \PDO::PARAM_INT);
            $st->bindValue('min_common_words', $minCommonWords, \PDO::PARAM_INT);
            $st->execute();
        } catch (\PDOException $e) {
            if ($this->isUnknownTableException($e)) {
                throw new EmptyIndexException(
                    'There are missing storage tables in the database. Is ' . __CLASS__ . '::erase() running in another process?',
                    0,
                    $e
                );
            }
            throw new UnknownException(sprintf(
                'Unknown exception with code "%s" occurred while fetching similar items: "%s".',
                $e->getCode(),
                $e->getMessage()
            ), 0, $e);
        }
        $recommendations = $st->fetchAll(\PDO::FETCH_ASSOC);

        return $recommendations;
    }

    /**
     * {@inheritdoc}
     *
     * @throws EmptyIndexException
     */
    public function getIndexStat(): array
    {
        $tableNames = array_map(fn($s) => $this->pdo->quote($this->getTableName($s)), array_keys(self::DEFAULT_TABLE_NAMES));

        $sql = "
            SELECT
                pg_total_relation_size(c.oid) AS total_size,
                c.reltuples AS row_count
            FROM
                pg_class c
            LEFT JOIN
                pg_namespace n ON n.oid = c.relnamespace
            WHERE
                c.relkind IN ('r', 'p')
                AND n.nspname = 'public'
                AND c.relname IN  (" . implode(',', $tableNames) . ');';

        $tableStatuses = $this->pdo->query($sql)->fetchAll();

        if (\count($tableStatuses) !== \count($tableNames)) {
            throw new EmptyIndexException(sprintf(
                'Some storage tables are missed in the database. Call %s::erase() first.',
                PdoStorage::class
            ));
        }

        $indexSize = 0;
        $indexRows = 0;
        foreach ($tableStatuses as $tableStatus) {
            $indexSize += $tableStatus['total_size'];
            $indexRows += max(0, $tableStatus['row_count']);
        }

        return [
            'bytes' => $indexSize,
            'rows'  => $indexRows,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function isUnknownTableException(\PDOException $e): bool
    {
        return $e->getCode() === '42P01';
    }

    /**
     * {@inheritdoc}
     */
    protected function isLockWaitingException(\PDOException $e): bool
    {
        return 7 === $e->errorInfo[1] && ('55P03' === $e->errorInfo[0] || '40P01' === $e->errorInfo[0]);
    }

    /**
     * {@inheritdoc}
     */
    protected function isUnknownColumnException(\PDOException $e): bool
    {
        return $e->getCode() === '42703'; // e.g. SQLSTATE[42703]: Undefined column: 7 ERROR:  column f.positions does not exist...
    }

    private function dropAndCreateTables(): void
    {
        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::TOC) . ';');
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::TOC) . ' (
            id SERIAL PRIMARY KEY,
            external_id VARCHAR(255) NOT NULL,
            instance_id INT NOT NULL DEFAULT 0,
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
			toc_id SERIAL PRIMARY KEY,
			word_count INT NOT NULL,
			images JSON NOT NULL
		)');

        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::SNIPPET) . ';');
        // TODO compression?
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::SNIPPET) . ' (
            toc_id INT NOT NULL,
            max_word_pos INT NOT NULL,
            min_word_pos INT NOT NULL,
            format_id INT NOT NULL,
            snippet TEXT NOT NULL,
            PRIMARY KEY (toc_id, max_word_pos)
		)');

        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::FULLTEXT_INDEX) . ';');
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::FULLTEXT_INDEX) . ' (
            word_id INT NOT NULL,
            toc_id INT NOT NULL,
            positions TEXT NOT NULL,
            PRIMARY KEY (word_id, toc_id)
		)');
        $this->pdo->exec(sprintf(
            'CREATE INDEX idx_%1$s_toc_id ON %1$s (toc_id);',
            $this->getTableName(self::FULLTEXT_INDEX)
        ));

        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::WORD) . ';');
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::WORD) . ' (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL DEFAULT \'\',
            UNIQUE (name)
		)');
    }
}
