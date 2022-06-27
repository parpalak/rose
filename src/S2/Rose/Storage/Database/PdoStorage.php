<?php
/** @noinspection PhpComposerExtensionStubsInspection */

/**
 * @copyright    2016-2020 Roman Parpalak
 * @license      MIT
 */

namespace S2\Rose\Storage\Database;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\ExternalIdCollection;
use S2\Rose\Entity\TocEntry;
use S2\Rose\Entity\TocEntryWithExternalId;
use S2\Rose\Exception\LogicException;
use S2\Rose\Exception\UnknownException;
use S2\Rose\Exception\UnknownIdException;
use S2\Rose\Finder;
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
    /**
     * @var array
     */
    protected $cachedWordIds = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var IdMappingStorage
     */
    protected $mapping;

    /**
     * @var MysqlRepository
     */
    protected $repository;

    /**
     * @param \PDO   $pdo
     * @param string $prefix
     * @param array  $options
     *
     * @throws InvalidEnvironmentException
     */
    public function __construct(\PDO $pdo, $prefix = 's2_rose_', array $options = [])
    {
        $driverName = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        switch ($driverName) {
            case 'mysql':
                $this->repository = new MysqlRepository($pdo, $prefix, $options);
                break;

            default:
                throw new InvalidEnvironmentException(sprintf('Driver "%s" is not supported.', $driverName));
        }
        $this->mapping = new IdMappingStorage();
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidEnvironmentException
     * @throws UnknownException
     */
    public function erase()
    {
        $this->mapping->clear();
        $this->cachedWordIds = [];
        $this->repository->erase();
    }

    /**
     * {@inheritdoc}
     *
     * @return FulltextIndexContent
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function fulltextResultByWords(array $words, $instanceId = null)
    {
        $result = new FulltextIndexContent();
        if (empty($words)) {
            return $result;
        }

        $data = $this->repository->findFulltextByWords($words, $instanceId);

        foreach ($data as $row) {
            $result->add($row['word'], $this->getExternalIdFromRow($row), $row['position']);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function getSingleKeywordIndexByWords(array $words, $instanceId = null)
    {
        $data      = $this->repository->findSingleKeywordIndex($words, $instanceId);
        $threshold = Finder::fulltextRateExcludeNum($this->getTocSize($instanceId));

        /** @var KeywordIndexContent[]|array $result */
        $result = [];
        foreach ($data as $row) {
            if ($row['type'] === Finder::TYPE_TITLE && $row['usage_num'] > $threshold) {
                continue;
            }

            if (!isset($result[$row['keyword']])) {
                $result[$row['keyword']] = new KeywordIndexContent();
            }

            // TODO Making items unique seems to be a hack for caller. Rewrite indexing using INSERT IGNORE?  @see \S2\Rose\Storage\KeywordIndexContent::add
            $result[$row['keyword']]->add($this->getExternalIdFromRow($row), $row['type']);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     * @throws UnknownException
     * @throws EmptyIndexException
     */
    public function getMultipleKeywordIndexByString($string, $instanceId = null)
    {
        $data = $this->repository->findMultipleKeywordIndex($string, $instanceId);

        $result = new KeywordIndexContent();
        foreach ($data as $row) {
            $result->add($this->getExternalIdFromRow($row), $row['type']);
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

        $this->repository->insertFulltext($words, $wordIds, $internalId);
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
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function removeFromIndex(ExternalId $externalId)
    {
        $this->repository->removeFromIndex($externalId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function addEntryToToc(TocEntry $entry, ExternalId $externalId)
    {
        $this->repository->addToToc($entry, $externalId);

        try {
            $internalId = $this->getInternalIdFromExternalId($externalId);
        } catch (UnknownIdException $e) {
            $internalId = $this->repository->selectInternalId($externalId);
            $this->mapping->add($externalId, $internalId);
        }

        $entry->setInternalId($internalId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function getTocByExternalIds(ExternalIdCollection $externalIds, $instanceId = null)
    {
        $data = $this->repository->getTocEntries(['ids' => $externalIds]);

        $result = [];
        foreach ($data as $row) {
            $date = null;
            if (isset($row['added_at'])) {
                try {
                    $date = new \DateTime($row['added_at']);
                } catch (\Exception $e) {
                }
            }

            $tocEntry = new TocEntry(
                $row['title'],
                $row['description'],
                $date,
                $row['url'],
                $row['hash']
            );
            $tocEntry->setInternalId($row['id']);

            $result[] = new TocEntryWithExternalId($tocEntry, $this->getExternalIdFromRow($row));
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function getTocByExternalId(ExternalId $externalId)
    {
        $entries = $this->getTocByExternalIds(new ExternalIdCollection([$externalId]));

        return count($entries) > 0 ? $entries[0]->getTocEntry() : null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function getTocSize($instanceId)
    {
        return $this->repository->getTocSize($instanceId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws EmptyIndexException
     * @throws UnknownException
     */
    public function removeFromToc(ExternalId $externalId)
    {
        $this->repository->removeFromToc($externalId);
        $this->mapping->remove($externalId);
    }

    /**
     * {@inheritdoc}
     * @throws UnknownException
     */
    public function startTransaction()
    {
        $this->mapping->clear();
        $this->repository->startTransaction();
    }

    /**
     * {@inheritdoc}
     * @throws UnknownException
     */
    public function commitTransaction()
    {
        $this->repository->commitTransaction();
        $this->mapping->clear();
    }

    /**
     * {@inheritdoc}
     * @throws UnknownException
     */
    public function rollbackTransaction()
    {
        $this->repository->rollbackTransaction();
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

        $ids = $this->repository->findIdsByWords(array_keys($unknownWords));
        foreach ($ids as $word => $id) {
            $this->cachedWordIds[$word] = $id;
            $knownWords[$word]          = $id;
            unset($unknownWords[$word]);
        }

        if (empty($unknownWords)) {
            return $knownWords;
        }

        $this->repository->insertWords(array_keys($unknownWords));

        $ids = $this->repository->findIdsByWords(array_keys($unknownWords));
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
     */
    private function addKeywordToDb($word, ExternalId $externalId, $type, $tableKey)
    {
        $internalId = $this->getInternalIdFromExternalId($externalId);
        $this->repository->insertKeywords([$word], $internalId, $type, $tableKey);
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

    /**
     * @param array $row
     *
     * @return ExternalId
     */
    private function getExternalIdFromRow($row)
    {
        return new ExternalId($row['external_id'], $row['instance_id'] > 0 ? $row['instance_id'] : null);
    }
}
