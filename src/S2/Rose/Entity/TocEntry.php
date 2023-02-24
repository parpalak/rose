<?php declare(strict_types=1);
/**
 * @copyright 2016-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

class TocEntry
{
    protected ?int $internalId = null;
    protected string $title = '';
    protected string $description = '';
    protected ?\DateTime $date;
    protected string $url = '';
    protected string $hash;
    private float $relevanceRatio;

    public function __construct(string $title, string $description, ?\DateTime $date, string $url, float $relevanceRatio, string $hash)
    {
        $this->title          = $title;
        $this->description    = $description;
        $this->date           = $date;
        $this->url            = $url;
        $this->relevanceRatio = $relevanceRatio;
        $this->hash           = $hash;
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

    public function getInternalId(): ?int
    {
        return $this->internalId;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @deprecated Make immutable
     */
    public function setInternalId(int $internalId): self
    {
        $this->internalId = $internalId;

        return $this;
    }

    public function getFormattedDate(): ?string
    {
        return $this->date !== null ? $this->date->format('Y-m-d H:i:s') : null;
    }

    public function getTimeZone(): ?string
    {
        return $this->date !== null ? $this->date->getTimeZone()->getName() : null;
    }
}
