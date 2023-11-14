<?php
/**
 * Fulltext search
 *
 * @copyright 2010-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\ExternalIdCollection;
use S2\Rose\Entity\FulltextQuery;
use S2\Rose\Entity\FulltextResult;
use S2\Rose\Entity\Query;
use S2\Rose\Entity\ResultSet;
use S2\Rose\Exception\ImmutableException;
use S2\Rose\Exception\LogicException;
use S2\Rose\Exception\UnknownIdException;
use S2\Rose\Snippet\SnippetBuilder;
use S2\Rose\Stemmer\StemmerInterface;
use S2\Rose\Storage\Dto\SnippetQuery;
use S2\Rose\Storage\StorageReadInterface;

class Finder
{
    protected StorageReadInterface $storage;
    protected StemmerInterface $stemmer;
    protected ?string $highlightTemplate = null;
    protected ?string $snippetLineSeparator = null;

    public function __construct(StorageReadInterface $storage, StemmerInterface $stemmer)
    {
        $this->storage = $storage;
        $this->stemmer = $stemmer;
    }

    public function setHighlightTemplate(string $highlightTemplate): self
    {
        $this->highlightTemplate = $highlightTemplate;

        return $this;
    }

    public function setSnippetLineSeparator(string $snippetLineSeparator): self
    {
        $this->snippetLineSeparator = $snippetLineSeparator;

        return $this;
    }

    /**
     * @throws ImmutableException
     */
    public function find(Query $query, bool $isDebug = false): ResultSet
    {
        $resultSet = new ResultSet($query->getLimit(), $query->getOffset(), $isDebug);
        if ($this->highlightTemplate !== null) {
            $resultSet->setHighlightTemplate($this->highlightTemplate);
        }

        $rawWords = $query->valueToArray();
        $resultSet->addProfilePoint('Input cleanup');

        if (\count($rawWords) > 0) {
            $this->findFulltext($rawWords, $query->getInstanceId(), $resultSet);
            $resultSet->addProfilePoint('Fulltext search');
        }

        $resultSet->freeze();

        $foundExternalIds = $resultSet->getFoundExternalIds();
        foreach ($this->storage->getTocByExternalIds($foundExternalIds) as $tocEntryWithExternalId) {
            $resultSet->attachToc($tocEntryWithExternalId);
        }

        $resultSet->addProfilePoint('Fetch TOC');

        $resultSet->removeDataWithoutToc();

        $relevanceByExternalIds = $resultSet->getSortedRelevanceByExternalId();

        if (\count($relevanceByExternalIds) > 0) {
            $this->buildSnippets($relevanceByExternalIds, $resultSet);
        }

        return $resultSet;
    }

    /**
     * Ignore frequent words encountering in indexed items.
     */
    public static function fulltextRateExcludeNum(int $tocSize): int
    {
        return max($tocSize * 0.5, 20);
    }

    /**
     * @throws ImmutableException
     */
    protected function findFulltext(array $words, ?int $instanceId, ResultSet $resultSet): void
    {
        $fulltextQuery        = new FulltextQuery($words, $this->stemmer);
        $fulltextIndexContent = $this->storage->fulltextResultByWords($fulltextQuery->getWordsWithStems(), $instanceId);
        $fulltextResult       = new FulltextResult(
            $fulltextQuery,
            $fulltextIndexContent,
            $this->storage->getTocSize($instanceId)
        );

        $fulltextResult->fillResultSet($resultSet);
    }

    public function buildSnippets(array $relevanceByExternalIds, ResultSet $resultSet): void
    {
        $snippetQuery = new SnippetQuery(ExternalIdCollection::fromStringArray(array_keys($relevanceByExternalIds)));
        try {
            $foundWordPositionsByExternalId = $resultSet->getFoundWordPositionsByExternalId();
        } catch (ImmutableException $e) {
            throw new LogicException($e->getMessage(), 0, $e);
        }
        foreach ($foundWordPositionsByExternalId as $serializedExtId => $wordsInfo) {
            if (!isset($relevanceByExternalIds[$serializedExtId])) {
                // Out of limit and offset scope, no need to fetch snippets.
                continue;
            }
            $externalId   = ExternalId::fromString($serializedExtId);
            $allPositions = array_merge(...array_values($wordsInfo));
            $snippetQuery->attach($externalId, $allPositions);
        }
        $resultSet->addProfilePoint('Snippets: make query');

        $snippetResult = $this->storage->getSnippets($snippetQuery);

        $resultSet->addProfilePoint('Snippets: obtaining');

        $sb = new SnippetBuilder($this->stemmer, $this->snippetLineSeparator);
        try {
            $sb->attachSnippets($resultSet, $snippetResult);
        } catch (ImmutableException|UnknownIdException $e) {
            throw new LogicException($e->getMessage(), 0, $e);
        }

        $resultSet->addProfilePoint('Snippets: building');
    }
}
