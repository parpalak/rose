<?php
/**
 * @copyright 2016-2024 Roman Parpalak
 * @license   MIT
 */

declare(strict_types=1);

namespace S2\Rose\Entity;

use S2\Rose\Entity\Metadata\ImgCollection;
use S2\Rose\Entity\Metadata\SnippetSource;
use S2\Rose\Exception\InvalidArgumentException;
use S2\Rose\Exception\RuntimeException;
use S2\Rose\Stemmer\StemmerInterface;

class ResultItem
{
    protected string $id;
    protected ?int $instanceId;
    protected string $title = '';
    protected string $description = '';
    protected ?\DateTime $date;
    protected string $url = '';
    protected float $relevanceRatio;
    protected float $relevance = 0.0;
    protected ImgCollection $imgCollection;
    protected ?Snippet $snippet = null;
    protected string $highlightTemplate;
    protected array $foundWords = [];

    /**
     * @param string $id Id in external system
     */
    public function __construct(
        string        $id,
        ?int          $instanceId,
        string        $title,
        string        $description,
        ?\DateTime    $date,
        string        $url,
        float         $relevanceRatio,
        ImgCollection $imgCollection,
        string        $highlightTemplate
    ) {
        $this->id                = $id;
        $this->instanceId        = $instanceId;
        $this->title             = $title;
        $this->description       = $description;
        $this->date              = $date;
        $this->url               = $url;
        $this->relevanceRatio    = $relevanceRatio;
        $this->imgCollection     = $imgCollection;
        $this->highlightTemplate = $highlightTemplate;
    }

    public function setSnippet(Snippet $snippet): self
    {
        $this->snippet = $snippet;

        return $this;
    }

    public function setRelevance(float $relevance): self
    {
        $this->relevance = $relevance;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getInstanceId(): ?int
    {
        return $this->instanceId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getRelevanceRatio(): float
    {
        return $this->relevanceRatio;
    }

    public function getRelevance(): float
    {
        return $this->relevance;
    }

    public function getSnippet(): string
    {
        if ($this->snippet === null) {
            return $this->description;
        }

        $snippet = $this->snippet->toString();
        if ($snippet) {
            return $snippet;
        }

        return $this->description ?: $this->snippet->getTextIntroduction();
    }

    public function getFormattedSnippet(): string
    {
        if ($this->snippet === null) {
            return $this->description;
        }

        $snippet = $this->snippet->toString(true);
        if ($snippet) {
            return $snippet;
        }

        return $this->description ?: $this->snippet->getTextIntroduction();
    }

    /**
     * @param string[] $words
     */
    public function setFoundWords(array $words): self
    {
        $this->foundWords = $words;

        return $this;
    }

    /**
     * @throws RuntimeException
     */
    public function getHighlightedTitle(StemmerInterface $stemmer): string
    {
        $template = $this->highlightTemplate;

        if (strpos($template, '%s') === false) {
            throw new InvalidArgumentException('Highlight template must contain "%s" substring for sprintf() function.');
        }

        $snippetLine = new SnippetLine(
            $this->title,
            SnippetSource::FORMAT_PLAIN_TEXT,
            $stemmer,
            $this->foundWords,
            0
        );

        return $snippetLine->getHighlighted($template, false);
    }

    public function getImageCollection(): ImgCollection
    {
        return $this->imgCollection;
    }
}
