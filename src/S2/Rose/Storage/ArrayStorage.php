<?php
/**
 * @copyright 2016-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\ExternalIdCollection;
use S2\Rose\Entity\Metadata\ImgCollection;
use S2\Rose\Entity\Metadata\SnippetSource;
use S2\Rose\Entity\TocEntry;
use S2\Rose\Entity\TocEntryWithMetadata;
use S2\Rose\Exception\LogicException;
use S2\Rose\Exception\UnknownIdException;
use S2\Rose\Finder;
use S2\Rose\Storage\Dto\SnippetQuery;
use S2\Rose\Storage\Dto\SnippetResult;

abstract class ArrayStorage implements StorageReadInterface, StorageWriteInterface
{
    protected array $excludedWords = [];
    protected array $metadata = [];

    /**
     * @var TocEntry[]
     */
    protected array $toc = [];
    protected FulltextProxyInterface $fulltextProxy;

    /**
     * @var array|ExternalId
     */
    protected $externalIdMap = [];

    /**
     * {@inheritdoc}
     */
    public function fulltextResultByWords(array $words, ?int $instanceId): FulltextIndexContent
    {
        $result = new FulltextIndexContent();
        foreach ($words as $word) {
            $data = $this->fulltextProxy->getByWord($word);
            foreach ($data as $id => $positionsByType) {
                $externalId = $this->externalIdFromInternalId($id);
                if ($externalId === null) {
                    continue;
                }
                if ($instanceId === null || $externalId->getInstanceId() === $instanceId) {
                    $result->add($word, new FulltextIndexPositionBag(
                        $externalId,
                        $positionsByType[FulltextProxyInterface::TYPE_TITLE] ?? [],
                        $positionsByType[FulltextProxyInterface::TYPE_KEYWORD] ?? [],
                        $positionsByType[FulltextProxyInterface::TYPE_CONTENT] ?? [],
                        isset($this->metadata[$id]) ? $this->metadata[$id]['wordCount'] : 0
                    ));
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     * @throws UnknownIdException
     */
    public function getSnippets(SnippetQuery $snippetQuery): SnippetResult
    {
        $result = new SnippetResult();
        $snippetQuery->iterate(function (ExternalId $externalId, array $positions) use ($result) {
            $fallbackCount = 0;
            foreach ($this->metadata[$this->internalIdFromExternalId($externalId)]['snippets'] ?? [] as $snippetSource) {
                if (!$snippetSource instanceof SnippetSource) {
                    throw new LogicException('Snippets must be stored as array of SnippetSource.');
                }
                if ($fallbackCount < 2 || $snippetSource->coversOneOfPositions($positions)) {
                    $result->attach($externalId, $snippetSource);
                    $fallbackCount++;
                }
            }
        });

        return $result;
    }

    /**
     * {@inheritdoc}
     * @throws UnknownIdException
     */
    public function addToFulltextIndex(array $titleWords, array $keywords, array $contentWords, ExternalId $externalId): void
    {
        $id = $this->internalIdFromExternalId($externalId);
        foreach ($titleWords as $position => $word) {
            $this->fulltextProxy->addWord($word, $id, FulltextProxyInterface::TYPE_TITLE, (int)$position);
        }
        foreach ($keywords as $position => $word) {
            $this->fulltextProxy->addWord($word, $id, FulltextProxyInterface::TYPE_KEYWORD, (int)$position);
        }
        foreach ($contentWords as $position => $word) {
            $this->fulltextProxy->addWord($word, $id, FulltextProxyInterface::TYPE_CONTENT, (int)$position);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isExcludedWord(string $word): bool
    {
        return isset($this->excludedWords[$word]);
    }

    /**
     * Drops frequent words from index.
     */
    public function cleanup(): void
    {
        $threshold = Finder::fulltextRateExcludeNum(\count($this->toc));

        foreach ($this->fulltextProxy->getFrequentWords($threshold) as $word => $stat) {
            // Drop fulltext frequent or empty items
            $this->fulltextProxy->removeWord($word);
            $this->excludedWords[$word] = 1;
        }
    }

    /**
     * {@inheritdoc}
     * @throws UnknownIdException
     */
    public function removeFromIndex(ExternalId $externalId): void
    {
        $internalId = $this->internalIdFromExternalId($externalId);

        $this->fulltextProxy->removeById($internalId);

        foreach ($this->metadata as &$data) {
            if (isset($data[$internalId])) {
                unset($data[$internalId]);
            }
        }
        unset($data);
    }

    /**
     * {@inheritdoc}
     */
    public function addEntryToToc(TocEntry $entry, ExternalId $externalId): void
    {
        try {
            $internalId = $this->internalIdFromExternalId($externalId);
            $this->removeFromToc($externalId);
        } catch (UnknownIdException $e) {
            $internalId = 0;
            foreach ($this->toc as $existingEntry) {
                $internalId = max($internalId, $existingEntry->getInternalId());
            }
            $internalId++;
        }

        $entry->setInternalId($internalId);

        $this->toc[$externalId->toString()] = $entry;
        $this->externalIdMap[$internalId]   = $externalId;
    }

    /**
     * {@inheritdoc}
     * @throws UnknownIdException
     */
    public function addMetadata(ExternalId $externalId, int $wordCount, ImgCollection $imgCollection): void
    {
        $internalId                               = $this->internalIdFromExternalId($externalId);
        $this->metadata[$internalId]['wordCount'] = $wordCount;
        $this->metadata[$internalId]['images']    = $imgCollection;
    }

    /**
     * @throws UnknownIdException
     */
    public function addSnippets(ExternalId $externalId, SnippetSource ...$snippets): void
    {
        if (\count($snippets) === 0) {
            return;
        }
        $this->metadata[$this->internalIdFromExternalId($externalId)]['snippets'] = $snippets;
    }

    /**
     * {@inheritdoc}
     */
    public function getTocByExternalIds(ExternalIdCollection $externalIds): array
    {
        $result = [];
        foreach ($externalIds->toArray() as $externalId) {
            $serializedExtId = $externalId->toString();
            if (isset($this->toc[$serializedExtId])) {
                $result[] = new TocEntryWithMetadata(
                    $this->toc[$serializedExtId],
                    $externalId,
                    $this->metadata[$this->toc[$serializedExtId]->getInternalId()]['images'] ?? new ImgCollection()
                );
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getTocByExternalId(ExternalId $externalId): ?TocEntry
    {
        $serializedExtId = $externalId->toString();

        return $this->toc[$serializedExtId] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function removeFromToc(ExternalId $externalId): void
    {
        $serializedExtId = $externalId->toString();
        if (!isset($this->toc[$serializedExtId])) {
            return;
        }

        $internalId = $this->toc[$serializedExtId]->getInternalId();
        unset($this->externalIdMap[$internalId], $this->toc[$serializedExtId]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTocSize(?int $instanceId): int
    {
        return \count($this->toc);
    }

    /**
     * @throws UnknownIdException
     */
    private function internalIdFromExternalId(ExternalId $externalId): int
    {
        $serializedExtId = $externalId->toString();
        if (!isset($this->toc[$serializedExtId])) {
            throw UnknownIdException::createIndexMissingExternalId($externalId);
        }

        return $this->toc[$serializedExtId]->getInternalId();
    }

    private function externalIdFromInternalId(int $internalId): ?ExternalId
    {
        return $this->externalIdMap[$internalId] ?? null;
    }
}
