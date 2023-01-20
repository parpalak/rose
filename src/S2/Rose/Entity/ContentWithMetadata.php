<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

use S2\Rose\Entity\Metadata\Img;
use S2\Rose\Entity\Metadata\SentenceMap;

class ContentWithMetadata
{
    private SentenceMap $sentenceMap;

    /**
     * @var Img[]
     */
    private array $images = [];

    public function __construct(SentenceMap $sentenceMap)
    {
        $this->sentenceMap = $sentenceMap;
    }

    public function attachImages(Img ...$images): self
    {
        $this->images = array_merge($this->images, $images);

        return $this;
    }

    public function getSentenceMap(): SentenceMap
    {
        return $this->sentenceMap;
    }
}
