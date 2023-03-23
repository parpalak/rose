<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity\Metadata;

class Img implements \JsonSerializable
{
    private string $src;
    private string $width;
    private string $height;
    private string $alt;

    public function __construct(string $src, string $width, string $height, string $alt)
    {
        $this->src    = $src;
        $this->width  = $width;
        $this->height = $height;
        $this->alt    = $alt;
    }

    public function getSrc(): string
    {
        return $this->src;
    }

    public function getWidth(): string
    {
        return $this->width;
    }

    public function getHeight(): string
    {
        return $this->height;
    }

    public function getAlt(): string
    {
        return $this->alt;
    }

    public static function fromArray(array $img): Img
    {
        return new self($img['src'], $img['width'], $img['height'], $img['alt']);
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    public function hasNumericDimensions(): bool
    {
        return is_numeric($this->width) && is_numeric($this->height);
    }
}
