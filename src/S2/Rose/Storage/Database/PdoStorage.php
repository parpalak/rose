<?php
/** @noinspection PhpComposerExtensionStubsInspection */

/**
 * @copyright    2016-2023 Roman Parpalak
 * @license      MIT
 */

namespace S2\Rose\Storage\Database;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\ExternalIdCollection;
use S2\Rose\Entity\Metadata\ImgCollection;
use S2\Rose\Entity\Metadata\SnippetSource;
use S2\Rose\Entity\TocEntry;
use S2\Rose\Entity\TocEntryWithMetadata;
use S2\Rose\Exception\LogicException;
use S2\Rose\Exception\UnknownException;
use S2\Rose\Exception\UnknownIdException;
use S2\Rose\Storage\Dto\SnippetQuery;
use S2\Rose\Storage\Dto\SnippetResult;
use S2\Rose\Storage\Exception\EmptyIndexException;
use S2\Rose\Storage\Exception\InvalidEnvironmentException;
use S2\Rose\Storage\FulltextIndexContent;
use S2\Rose\Storage\KeywordIndexContent;
use S2\Rose\Storage\StorageEraseInterface;
use S2\Rose\Storage\StorageReadInterface;
use S2\Rose\Storage\StorageWriteInterface;
use S2\Rose\Storage\TransactionalStorageInterface;

class PdoStorage implements StorageWriteInterface, StorageReadInterface, StorageEraseInterface, TransactionalStorageInterface
{
    protected array $cachedWordIds = [];
    protected array $options = [];
    protected IdMappingStorage $mapping;
    protected \PDO $pdo;
    protected string $prefix;
    protected ?AbstractRepository $repository = null;

    /**
     * @throws InvalidEnvironmentException
     */
    public function __construct(\PDO $pdo, string $prefix = 's2_rose_', array $options = [])
    {
        $this->pdo     = $pdo;
        $this->prefix  = $prefix;
        $this->options = $options;
        $this->mapping = new IdMappingStorage();
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidEnvironmentException
     * @throws UnknownException
     */
    public function erase(): void
    {
        $this->mapping->clear();
        $this->cachedWordIds = [];
        $this->getRepository()->erase();
    }

    /**
     * {@inheritdoc}
     *
     * @return FulltextIndexContent
     * @throws EmptyIndexException
     * @throws UnknownException
     * @throws InvalidEnvironmentException
     */
    public function fulltextResultByWords(array $words, $instanceId = null)
    {
        $result = new FulltextIndexContent();
        if (empty($words)) {
            return $result;
        }

        $data = $this->getRepository()->findFulltextByWords($words, $instanceId);

        foreach ($data as $row) {
            $result->add($row['word'], $this->getExternalIdFromRow($row), $row['positions'], $row['word_count']);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     * @throws EmptyIndexException
     * @throws UnknownException
     * @throws InvalidEnvironmentException
     */
    public function getSingleKeywordIndexByWords(array $words, $instanceId = null)
    {
        $data    = $this->getRepository()->findSingleKeywordIndex($words, $instanceId);
        $tocSize = $this->getTocSize($instanceId);

        /** @var KeywordIndexContent[]|array $result */
        $result = [];
        foreach ($data as $row) {
            if (!isset($result[$row['keyword']])) {
                $result[$row['keyword']] = new KeywordIndexContent();
            }

            // TODO Making items unique seems to be a hack for caller. Rewrite indexing using INSERT IGNORE?  @see \S2\Rose\Storage\KeywordIndexContent::add
            $result[$row['keyword']]->add($this->getExternalIdFromRow($row), $row['type'], $tocSize, $row['usage_num']);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     * @throws UnknownException
     * @throws EmptyIndexException
     * @throws InvalidEnvironmentException
     */
    public function getMultipleKeywordIndexByString($string, $instanceId = null)
    {
        $data = $this->getRepository()->findMultipleKeywordIndex($string, $instanceId);

        $result = new KeywordIndexContent();
        foreach ($data as $row) {
            $result->add($this->getExternalIdFromRow($row), $row['type']);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidEnvironmentException
     */
    public function getSnippets(SnippetQuery $snippetQuery): SnippetResult
    {
        $data = $this->getRepository()->getSnippets($snippetQuery);

        $result = new SnippetResult();
        foreach ($data as $row) {
            $result->attach($row['externalId'], new SnippetSource($row['snippet'], $row['format_id'] ?? SnippetSource::FORMAT_PLAIN_TEXT, $row['min_word_pos'], $row['max_word_pos']));
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @throws EmptyIndexException
     * @throws UnknownException
     * @throws UnknownIdException
     * @throws \S2\Rose\Exception\RuntimeException
     */
    public function addToFulltext(array $words, ExternalId $externalId)
    {
        if (empty($words)) {
            return;
        }

        $internalId = $this->getInternalIdFromExternalId($externalId);
        $wordIds    = $this->getWordIds($words);

        $this->getRepository()->insertFulltext($words, $wordIds, $internalId);
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
     *
     * @throws UnknownException
     * @throws UnknownIdException
     */
    public function addToSingleKeywordIndex($word, ExternalId $externalId, $type)
    {
        try {
            $this->addKeywordToDb($word, $externalId, $type, MysqlRepository::KEYWORD_INDEX);
        } catch (\PDOException $e) {
            throw new UnknownException('Unknown exception occurred while single keyword indexing:' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnknownException
     * @throws UnknownIdException
     */
    public function addToMultipleKeywordIndex($string, ExternalId $externalId, $type)
    {
        try {
            $this->addKeywordToDb($string, $externalId, $type, MysqlRepository::KEYWORD_MULTIPLE_INDEX);
        } catch (\PDOException $e) {
            throw new UnknownException('Unknown exception occurred while multiple keyword indexing:' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnknownIdException
     * @throws UnknownException
     * @throws InvalidEnvironmentException
     */
    public function addMetadata(ExternalId $externalId, int $wordCount, ImgCollection $imgCollection): void
    {
        $internalId = $this->getInternalIdFromExternalId($externalId);
        $this->getRepository()->insertMetadata($internalId, $wordCount, $imgCollection->toJson());
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnknownIdException
     * @throws InvalidEnvironmentException
     */
    public function addSnippets(ExternalId $externalId, SnippetSource ...$snippets): void
    {
        if (\count($snippets) === 0) {
            return;
        }
        $internalId = $this->getInternalIdFromExternalId($externalId);
        $this->getRepository()->insertSnippets($internalId, ...$snippets);
    }

    /**
     * {@inheritdoc}
     *
     * @throws EmptyIndexException
     * @throws UnknownException
     * @throws InvalidEnvironmentException
     */
    public function removeFromIndex(ExternalId $externalId)
    {
        $this->getRepository()->removeFromIndex($externalId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws EmptyIndexException
     * @throws UnknownException
     * @throws InvalidEnvironmentException
     */
    public function addEntryToToc(TocEntry $entry, ExternalId $externalId)
    {
        $this->getRepository()->addToToc($entry, $externalId);

        try {
            $internalId = $this->getInternalIdFromExternalId($externalId);
        } catch (UnknownIdException $e) {
            $internalId = $this->getRepository()->selectInternalId($externalId);
            $this->mapping->add($externalId, $internalId);
        }

        $entry->setInternalId($internalId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws EmptyIndexException
     * @throws UnknownException
     * @throws InvalidEnvironmentException
     */
    public function getTocByExternalIds(ExternalIdCollection $externalIds, $instanceId = null)
    {
        $data = $this->getRepository()->getTocEntries(['ids' => $externalIds]);

        return $this->transformDataToTocEntries($data);
    }

    /**
     * @param string $titlePrefix
     *
     * @return TocEntryWithMetadata[]
     * @throws InvalidEnvironmentException
     * @throws \JsonException
     */
    public function getTocByTitlePrefix($titlePrefix)
    {
        $data = $this->getRepository()->getTocEntries(['title' => $titlePrefix]);

        return $this->transformDataToTocEntries($data);
    }

    /**
     * {@inheritdoc}
     *
     * @throws EmptyIndexException
     * @throws UnknownException
     * @throws InvalidEnvironmentException
     */
    public function getTocByExternalId(ExternalId $externalId)
    {
        $entries = $this->getTocByExternalIds(new ExternalIdCollection([$externalId]));

        return \count($entries) > 0 ? $entries[0]->getTocEntry() : null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws EmptyIndexException
     * @throws UnknownException
     * @throws InvalidEnvironmentException
     */
    public function getTocSize($instanceId)
    {
        return $this->getRepository()->getTocSize($instanceId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws EmptyIndexException
     * @throws UnknownException
     * @throws InvalidEnvironmentException
     */
    public function removeFromToc(ExternalId $externalId)
    {
        $this->getRepository()->removeFromToc($externalId);
        $this->mapping->remove($externalId);
    }

    /**
     * @param ExternalId $externalId An id of indexed item to search other similar items
     * @param int|null   $instanceId Id of instance where to search these similar items
     * @param int        $minCommonWords Lower limit for common words. The less common words,
     *                                   the more items are returned, but among them the proportion
     *                                   of irrelevant items is increasing.
     * @param int        $limit
     *
     * @return array
     * @throws \JsonException
     * @throws InvalidEnvironmentException
     */
    public function getSimilar(ExternalId $externalId, ?int $instanceId = null, int $minCommonWords = 4, int $limit = 10): array
    {
        $data = $this->getRepository()->getSimilar($externalId, $instanceId, $minCommonWords, $limit);

        foreach ($data as &$row) {
            [$tocWithMetadata] = $this->transformDataToTocEntries([$row]);
            $row['tocWithMetadata'] = $tocWithMetadata;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     * @throws UnknownException
     * @throws InvalidEnvironmentException
     */
    public function startTransaction()
    {
        $this->mapping->clear();
        $this->getRepository()->startTransaction();
    }

    /**
     * {@inheritdoc}
     * @throws UnknownException
     * @throws InvalidEnvironmentException
     */
    public function commitTransaction()
    {
        $this->getRepository()->commitTransaction();
        $this->mapping->clear();
    }

    /**
     * {@inheritdoc}
     * @throws UnknownException
     * @throws InvalidEnvironmentException
     */
    public function rollbackTransaction()
    {
        $this->getRepository()->rollbackTransaction();
    }

    /**
     * @return array
     * @throws InvalidEnvironmentException
     */
    public function getIndexStat()
    {
        return $this->getRepository()->getIndexStat();
    }

    /**
     * TODO move to another class
     *
     * @param string[] $words
     *
     * @return int[]
     * @throws EmptyIndexException
     * @throws UnknownException
     * @throws \S2\Rose\Exception\RuntimeException
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

        $ids = $this->getRepository()->findIdsByWords(array_keys($unknownWords));
        foreach ($ids as $word => $id) {
            $this->cachedWordIds[$word] = $id;
            $knownWords[$word]          = $id;
            unset($unknownWords[$word]);
        }

        if (empty($unknownWords)) {
            return $knownWords;
        }

        $this->getRepository()->insertWords(array_keys($unknownWords));

        $ids = $this->getRepository()->findIdsByWords(array_keys($unknownWords));
        foreach ($ids as $word => $id) {
            $this->cachedWordIds[$word] = $id;
            $knownWords[$word]          = $id;
            unset($unknownWords[$word]);
        }

        if (empty($unknownWords)) {
            return $knownWords;
        }

        throw new LogicException('Inserted words not found. Unknown words: ' . var_export($unknownWords, true));
    }

    /**
     * @param string     $word
     * @param ExternalId $externalId
     * @param int        $type
     * @param string     $tableKey
     *
     * @throws UnknownIdException
     * @throws InvalidEnvironmentException
     */
    private function addKeywordToDb($word, ExternalId $externalId, $type, $tableKey)
    {
        $internalId = $this->getInternalIdFromExternalId($externalId);
        $this->getRepository()->insertKeywords([$word], $internalId, $type, $tableKey);
    }

    /**
     * @param ExternalId $externalId
     *
     * @return int
     * @throws UnknownIdException
     */
    private function getInternalIdFromExternalId(ExternalId $externalId)
    {
        if (!($id = $this->mapping->get($externalId))) {
            throw UnknownIdException::createIndexMissingExternalId($externalId);
        }

        return $id;
    }

    private function getExternalIdFromRow(array $row): ExternalId
    {
        return new ExternalId($row['external_id'], $row['instance_id'] > 0 ? $row['instance_id'] : null);
    }

    /**
     * @return TocEntryWithMetadata[]
     * @throws \JsonException
     */
    private function transformDataToTocEntries(array $data): array
    {
        $result = [];
        foreach ($data as $row) {
            $date = null;
            if (isset($row['added_at'])) {
                try {
                    $date = new \DateTime($row['added_at'], isset($row['timezone']) ? new \DateTimeZone($row['timezone']) : null);
                } catch (\Exception $e) {
                }
            }

            $tocEntry = new TocEntry(
                $row['title'],
                $row['description'],
                $date,
                $row['url'],
                (float)$row['relevance_ratio'],
                $row['hash']
            );
            $tocEntry->setInternalId($row['id']);

            $imgCollection = isset($row['images']) ? ImgCollection::createFromJson($row['images']) : new ImgCollection();
            $result[]      = new TocEntryWithMetadata($tocEntry, $this->getExternalIdFromRow($row), $imgCollection);
        }

        return $result;
    }

    /**
     * @throws InvalidEnvironmentException
     */
    private function getRepository(): AbstractRepository
    {
        return $this->repository ?? $this->repository = AbstractRepository::create($this->pdo, $this->prefix, $this->options);
    }
}
