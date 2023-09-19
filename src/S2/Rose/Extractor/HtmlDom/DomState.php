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
use S2\Rose\Entity\Metadata\SnippetSource;
use S2\Rose\Helper\StringHelper;

class DomState
{
    private const ALLOWED_FORMATTING = [
        StringHelper::BOLD,
        StringHelper::ITALIC,
        StringHelper::SUPERSCRIPT,
        StringHelper::SUBSCRIPT,
    ];

    private bool $startNewParagraph = false;
    private int $currentParagraphIndex = 0;
    private array $formattingLevel = [];
    private string $pendingFormatting = '';
    private SentenceMap $sentenceMap;
    /**
     * @var Img[]
     */
    private array $images = [];

    public function __construct()
    {
        $this->sentenceMap = new SentenceMap(SnippetSource::FORMAT_INTERNAL);
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

        $textContent             = $this->pendingFormatting . str_replace('\\', '\\\\', $textContent);
        $this->pendingFormatting = '';

        $this->sentenceMap->add($this->currentParagraphIndex, $path, $textContent);
    }

    public function startNewParagraph(): void
    {
        $this->startNewParagraph = true;
    }

    public function startFormatting(string $formatting): void
    {
        if (!\in_array($formatting, self::ALLOWED_FORMATTING, true)) {
            throw new \LogicException(sprintf('Unknown formatting "%s".', $formatting));
        }
        $this->formattingLevel[$formatting] = 1 + ($this->formattingLevel[$formatting] ?? 0);
        if ($this->formattingLevel[$formatting] === 1) {
            $this->pendingFormatting .= '\\' . $formatting;
        }
    }

    public function stopFormatting(string $formatting): void
    {
        if (!\in_array($formatting, self::ALLOWED_FORMATTING, true)) {
            throw new \LogicException(sprintf('Unknown formatting "%s".', $formatting));
        }
        $level = $this->formattingLevel[$formatting] ?? 0;
        if ($level === 1) {
            if ($this->pendingFormatting === '') {
                // No format symbols are queued. This means that symbols of formatting start have already been added
                // to SentenceMap. So it is not empty and the last item can be modified.
                $this->sentenceMap->appendToLastItem('\\' . strtoupper($formatting));
            } else {
                $this->pendingFormatting .= '\\' . strtoupper($formatting);
            }
        }
        if ($level > 0) {
            $this->formattingLevel[$formatting] = $level - 1;
        }
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
