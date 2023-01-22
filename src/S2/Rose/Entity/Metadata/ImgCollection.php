<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity\Metadata;

class ImgCollection
{
    private array $images;

    public function __construct(Img ...$images)
    {
        $this->images = $images;
    }

    /**
     * @throws \JsonException
     */
    public static function createFromJson(string $json): self
    {
        return new self(...array_map(static fn(array $item) => Img::fromArray($item), json_decode($json, true, 512, JSON_THROW_ON_ERROR)));
    }

    public function toJson(): string
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return json_encode($this->images, JSON_THROW_ON_ERROR);
    }

    public function offsetGet(int $int): ?Img
    {
        return $this->images[$int] ?? null;
    }
}
