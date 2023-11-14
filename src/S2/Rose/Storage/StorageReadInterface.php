<?php
/**
 * @copyright 2016-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

use S2\Rose\Entity\ExternalIdCollection;
use S2\Rose\Entity\TocEntryWithMetadata;
use S2\Rose\Storage\Dto\SnippetResult;
use S2\Rose\Storage\Dto\SnippetQuery;

interface StorageReadInterface
{
    /**
     * @param string[] $words
     */
    public function fulltextResultByWords(array $words, ?int $instanceId): FulltextIndexContent;

    public function isExcludedWord(string $word): bool;

    /**
     * @return TocEntryWithMetadata[]
     */
    public function getTocByExternalIds(ExternalIdCollection $externalIds): array;

    public function getSnippets(SnippetQuery $snippetQuery): SnippetResult;

    public function getTocSize(?int $instanceId): int;
}
