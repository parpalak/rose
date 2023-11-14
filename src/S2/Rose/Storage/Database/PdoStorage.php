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
use S2\Rose\Helper\StringHelper;
use S2\Rose\Storage\Dto\SnippetQuery;
use S2\Rose\Storage\Dto\SnippetResult;
use S2\Rose\Storage\Exception\EmptyIndexException;
use S2\Rose\Storage\Exception\InvalidEnvironmentException;
use S2\Rose\Storage\FulltextIndexContent;
use S2\Rose\Storage\FulltextIndexPositionBag;
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
    public function fulltextResultByWords(array $words, $instanceId = null): FulltextIndexContent
    {
        $result = new FulltextIndexContent();
        if (\count($words) === 0) {
            return $result;
        }

        $generator = $this->getRepository()->findFulltextByWords($words, $instanceId);

        foreach ($generator as $row) {
            $result->add($row['word'], new FulltextIndexPositionBag($this->getExternalIdFromRow($row), $row['title_positions'], $row['keyword_positions'], $row['content_positions'], $row['word_count']));
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
    public function addToFulltextIndex(array $titleWords, array $keywords, array $contentWords, ExternalId $externalId): void
    {
        if (empty($contentWords)) {
            return;
        }

        $internalId = $this->getInternalIdFromExternalId($externalId);
        $wordIds    = $this->getWordIds(array_merge(array_values($contentWords), array_values($titleWords), array_values($keywords)));

        /**
         * @see \S2\Rose\Entity\WordPositionContainer::compareArrays for sorting requirement
         */
        ksort($titleWords);
        ksort($keywords);
        ksort($contentWords);
        $this->getRepository()->insertFulltext($titleWords, $keywords, $contentWords, $wordIds, $internalId);
    }

    /**
     * {@inheritdoc}
     */
    public function isExcludedWord(string $word): bool
    {
        // Nothing is excluded in current DB storage implementation.
        return false;
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
    public function removeFromIndex(ExternalId $externalId): void
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
    public function addEntryToToc(TocEntry $entry, ExternalId $externalId): void
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
    public function getTocByExternalIds(ExternalIdCollection $externalIds): array
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
    public function getTocByExternalId(ExternalId $externalId): ?TocEntry
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
    public function getTocSize(?int $instanceId): int
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
    public function removeFromToc(ExternalId $externalId): void
    {
        $this->getRepository()->removeFromToc($externalId);
        $this->mapping->remove($externalId);
    }

    /**
     * @param ExternalId $externalId An id of indexed item to search other similar items
     * @param bool       $includeFormatting Switch the snippets to HTML formatting if available
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
    public function getSimilar(ExternalId $externalId, bool $includeFormatting, ?int $instanceId = null, int $minCommonWords = 4, int $limit = 10): array
    {
        $data = $this->getRepository()->getSimilar($externalId, $instanceId, $minCommonWords, $limit);

        foreach ($data as &$row) {
            [$tocWithMetadata] = $this->transformDataToTocEntries([$row]);
            $row['tocWithMetadata'] = $tocWithMetadata;
            if (!isset($row['snippet'])) {
                $row['snippet'] = '';
            }
            if (!isset($row['snippet2'])) {
                $row['snippet2'] = '';
            }
            // TODO take into account format_id of these snippets
            $row['snippet'] = $includeFormatting ? StringHelper::convertInternalFormattingToHtml($row['snippet']) : StringHelper::clearInternalFormatting($row['snippet']);
            $row['snippet2'] = $includeFormatting ? StringHelper::convertInternalFormattingToHtml($row['snippet2']) : StringHelper::clearInternalFormatting($row['snippet2']);
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
     * TODO move to another class?
     *
     * @param string[] $words
     *
     * @return int[]|array
     * @throws UnknownException
     * @throws \S2\Rose\Exception\RuntimeException
     */
    protected function getWordIds(array $words): array
    {
        $knownWords   = [];
        $unknownWords = [];
        foreach ($words as $word) {
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
