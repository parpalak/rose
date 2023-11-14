<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

use S2\Rose\Entity\ExternalId;

class FulltextIndexPositionBag
{
    private ExternalId $externalId;
    private array $titlePositions;
    private array $keywordPositions;
    private array $contentPositions;
    private int $wordCount;

    public function __construct(ExternalId $externalId, array $titlePositions, array $keywordPositions, array $contentPositions, int $wordCount)
    {
        $this->externalId       = $externalId;
        $this->titlePositions   = $titlePositions;
        $this->keywordPositions = $keywordPositions;
        $this->contentPositions = $contentPositions;
        $this->wordCount        = $wordCount;
    }

    public function getExternalId(): ExternalId
    {
        return $this->externalId;
    }

    public function getTitlePositions(): array
    {
        return $this->titlePositions;
    }

    public function getKeywordPositions(): array
    {
        return $this->keywordPositions;
    }

    public function getContentPositions(): array
    {
        return $this->contentPositions;
    }

    public function getWordCount(): int
    {
        return $this->wordCount;
    }
}
