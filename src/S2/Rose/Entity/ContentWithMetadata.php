<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

use S2\Rose\Entity\Metadata\ImgCollection;
use S2\Rose\Entity\Metadata\SentenceMap;

class ContentWithMetadata
{
    private SentenceMap $sentenceMap;
    private ImgCollection $imageCollection;

    public function __construct(SentenceMap $sentenceMap, ImgCollection $images)
    {
        $this->sentenceMap     = $sentenceMap;
        $this->imageCollection = $images;
    }

    public function getSentenceMap(): SentenceMap
    {
        return $this->sentenceMap;
    }

    public function getImageCollection(): ImgCollection
    {
        return $this->imageCollection;
    }
}
