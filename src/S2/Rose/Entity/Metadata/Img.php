<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity\Metadata;

class Img
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
}
