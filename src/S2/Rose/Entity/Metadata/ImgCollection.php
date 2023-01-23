<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity\Metadata;

class ImgCollection extends \ArrayIterator
{
    private array $images;

    public function __construct(Img ...$images)
    {
        parent::__construct($images);
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
        return json_encode($this->getArrayCopy(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
}
