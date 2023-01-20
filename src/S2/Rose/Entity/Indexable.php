<?php
/**
 * @copyright 2016-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

class Indexable
{
    protected ExternalId $externalId;
    protected string $title = '';
    protected string $content = '';
    protected string $keywords = '';
    protected string $description = '';
    protected ?\DateTime $date = null;
    protected string $url = '';
    protected float $relevanceRatio = 1.0;

    public function __construct(string $id, string $title, string $content, ?int $instanceId = null)
    {
        $this->externalId = new ExternalId($id, $instanceId);
        $this->title      = $title;
        $this->content    = $content;
    }

    public function getExternalId(): ExternalId
    {
        return $this->externalId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getKeywords(): string
    {
        return $this->keywords;
    }

    public function setKeywords(string $keywords): self
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date = null): self
    {
        $this->date = $date;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getRelevanceRatio(): float
    {
        return $this->relevanceRatio;
    }

    public function setRelevanceRatio(float $relevanceRatio): self
    {
        if ($relevanceRatio < 0.001) {
            throw new \DomainException('Relevance ratio must not be less than 0.001.');
        }
        if ($relevanceRatio > 9999) {
            throw new \DomainException('Relevance ratio must not be greater than 9999.');
        }

        $this->relevanceRatio = $relevanceRatio;

        return $this;
    }

    public function toTocEntry(): TocEntry
    {
        return new TocEntry(
            $this->getTitle(),
            $this->getDescription(),
            $this->getDate(),
            $this->getUrl(),
            $this->getRelevanceRatio(),
            $this->calcHash()
        );
    }

    public function calcHash(): string
    {
        return md5(serialize([
            $this->getTitle(),
            $this->getDescription(),
            $this->getKeywords(),
            $this->getContent(),
        ]));
    }
}
