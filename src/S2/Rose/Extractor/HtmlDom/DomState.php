<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Extractor\HtmlDom;

use S2\Rose\Entity\ContentWithMetadata;
use S2\Rose\Entity\Metadata\Img;
use S2\Rose\Entity\Metadata\ImgCollection;
use S2\Rose\Entity\Metadata\SentenceMap;

class DomState
{
    private bool $startNewParagraph = false;
    private int $currentParagraphIndex = 0;
    private SentenceMap $sentenceMap;
    /**
     * @var Img[]
     */
    private array $images = [];

    public function __construct()
    {
        $this->sentenceMap = new SentenceMap();
    }

    public function attachContent(string $path, string $textContent): void
    {
        if ($this->startNewParagraph) {
            $this->currentParagraphIndex++;
            $this->startNewParagraph = false;
        }

        /**
         * Decode all entities. '&' was encoded before and decoded in DOM processing.
         * @see \S2\Rose\Extractor\HtmlDom\DomExtractor::getDomDocument
         */
        $textContent = html_entity_decode($textContent, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);

        $this->sentenceMap->add($this->currentParagraphIndex, $path, $textContent);
    }

    public function startNewParagraph(): void
    {
        $this->startNewParagraph = true;
    }

    public function attachImg(string $src, string $width, string $height, string $alt): void
    {
        $this->images[] = new Img($src, $width, $height, $alt);
    }

    public function toContentWithMetadata(): ContentWithMetadata
    {
        return new ContentWithMetadata($this->sentenceMap, new ImgCollection(...$this->images));
    }
}
