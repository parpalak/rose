<?php
/**
 * @copyright 2016-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\Metadata\ImgCollection;
use S2\Rose\Entity\Metadata\SnippetSource;
use S2\Rose\Entity\TocEntry;

interface StorageWriteInterface
{
    /**
     * @param array $titleWords Keys are the positions of corresponding words.
     * @param array $keywords Keys are the positions of corresponding words.
     * @param array $contentWords Keys are the positions of corresponding words.
     */
    public function addToFulltextIndex(array $titleWords, array $keywords, array $contentWords, ExternalId $externalId): void;

    public function removeFromIndex(ExternalId $externalId): void;

    public function isExcludedWord(string $word): bool;

    public function addEntryToToc(TocEntry $entry, ExternalId $externalId): void;

    /**
     * TODO How can a read method be eliminated from the writer interface?
     */
    public function getTocByExternalId(ExternalId $externalId): ?TocEntry;

    public function removeFromToc(ExternalId $externalId): void;

    /**
     * Save some additional info about indexing items
     */
    public function addMetadata(ExternalId $externalId, int $wordCount, ImgCollection $imgCollection): void;

    public function addSnippets(ExternalId $externalId, SnippetSource ...$snippets): void;
}
