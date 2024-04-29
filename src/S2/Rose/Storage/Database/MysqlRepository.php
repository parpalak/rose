<?php /** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection PhpUnnecessaryLocalVariableInspection */
/** @noinspection SqlDialectInspection */
/**
 * @copyright 2020-2023 Roman Parpalak
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

class MysqlRepository extends AbstractRepository
{
    private const KEYLEN         = 255;
    private const UTF8MB4_KEYLEN = 191;

    /**
     * {@inheritdoc}
     *
     * @throws InvalidEnvironmentException
     * @throws RuntimeException
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
            if ($this->isLockWaitingException($e)) {
                throw new RuntimeException('Cannot drop and create tables. Possible deadlock? Database reported: ' . $e->getMessage(), 0, $e);
            }
            if ($e->getCode() === '42000') {
                throw new InvalidEnvironmentException($e->getMessage(), (int)$e->getCode(), $e);
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

        $sql = 'INSERT IGNORE INTO ' . $this->getTableName(self::WORD) . ' (name) VALUES ("' . implode(
                '"),("',
                array_map(static function ($x) {
                    return addslashes($x);
                }, $partWords)
            ) . '")';

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
            ' ON DUPLICATE KEY UPDATE title = :title2, description = :description2, added_at = :added_at2, timezone = :timezone2, url = :url2, relevance_ratio = :relevance_ratio2, hash = :hash2';

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                'external_id' => $externalId->getId(),
                'instance_id' => (int)$externalId->getInstanceId(),

                'title'           => $entry->getTitle(),
                'description'     => $entry->getDescription(),
                'added_at'        => $entry->getFormattedDate(),
                'timezone'        => $entry->getTimeZone(),
                'url'             => $entry->getUrl(),
                'relevance_ratio' => $entry->getRelevanceRatio(),
                'hash'            => $entry->getHash(),

                'title2'           => $entry->getTitle(),
                'description2'     => $entry->getDescription(),
                'added_at2'        => $entry->getFormattedDate(),
                'timezone2'        => $entry->getTimeZone(),
                'url2'             => $entry->getUrl(),
                'relevance_ratio2' => $entry->getRelevanceRatio(),
                'hash2'            => $entry->getHash(),
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
     * @throws UnknownException
     * @throws EmptyIndexException
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

        try {
            $st = $this->pdo->prepare($sql);
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

    /**
     * {@inheritdoc}
     */
    public static function prepareWords(array $words): array
    {
        $partWords = [];
        foreach ($words as $fullWord) {
            $partWords[$fullWord] = mb_substr((string)$fullWord, 0, self::UTF8MB4_KEYLEN);
        }

        return $partWords;
    }

    /**
     * {@inheritdoc}
     */
    protected function isUnknownTableException(\PDOException $e): bool
    {
        return $e->getCode() === '42S02';
    }

    /**
     * {@inheritdoc}
     */
    protected function isLockWaitingException(\PDOException $e): bool
    {
        return 1205 === (int)$e->errorInfo[1];
    }

    /**
     * {@inheritdoc}
     */
    protected function isUnknownColumnException(\PDOException $e): bool
    {
        return $e->getCode() === '42S22'; // e.g. SQLSTATE[42S22]: Column not found: 1054 Unknown column 'f.positions' in 'field list'
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
			timezone VARCHAR(64) NULL,
			url TEXT NOT NULL,
			relevance_ratio DECIMAL(4,3) NOT NULL,
			hash VARCHAR(80) NOT NULL DEFAULT "",
			PRIMARY KEY (`id`),
			UNIQUE KEY (instance_id, external_id(' . $keyLen . '))
		) ENGINE=InnoDB CHARACTER SET ' . $charset);

        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::METADATA) . ';');

        try {
            $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::METADATA) . ' (
                toc_id INT(11) UNSIGNED NOT NULL,
                word_count INT(11) UNSIGNED NOT NULL,
                images JSON NOT NULL,
                PRIMARY KEY (toc_id)
            ) ENGINE=InnoDB CHARACTER SET ' . $charset);
        } catch (\PDOException $e) {
            // Fallback for old MariaDB < 10.2 with no JSON alias support
            $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::METADATA) . ' (
                toc_id INT(11) UNSIGNED NOT NULL,
                word_count INT(11) UNSIGNED NOT NULL,
                images LONGTEXT NOT NULL,
                PRIMARY KEY (toc_id)
            ) ENGINE=InnoDB CHARACTER SET ' . $charset);
        }

        $this->pdo->exec('DROP TABLE IF EXISTS ' . $this->getTableName(self::SNIPPET) . ';');
        $this->pdo->exec('CREATE TABLE ' . $this->getTableName(self::SNIPPET) . ' (
			toc_id INT(11) UNSIGNED NOT NULL,
			max_word_pos INT(11) UNSIGNED NOT NULL,
			min_word_pos INT(11) UNSIGNED NOT NULL,
			format_id INT(11) UNSIGNED NOT NULL,
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
    }
}
