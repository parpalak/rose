<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity\Metadata;

use S2\Rose\Exception\InvalidArgumentException;

class SnippetSource
{
    private string $text;
    private int $minPosition;
    private int $maxPosition;

    public function __construct(string $text, int $minPosition, int $maxPosition)
    {
        if ($minPosition < 0) {
            throw new InvalidArgumentException('Word position cannot be negative.');
        }
        if ($minPosition > $maxPosition) {
            throw new InvalidArgumentException('Minimal word position cannot be greater than maximal.');
        }

        $this->text        = $text;
        $this->minPosition = $minPosition;
        $this->maxPosition = $maxPosition;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getMinPosition(): int
    {
        return $this->minPosition;
    }

    public function getMaxPosition(): int
    {
        return $this->maxPosition;
    }

    /**
     * @param int[] $positions
     */
    public function coversOneOfPositions(array $positions): bool
    {
        foreach ($positions as $position) {
            if ($position >= $this->minPosition && $position <= $this->maxPosition) {
                return true;
            }
        }

        return false;
    }

    public function __toString()
    {
        return $this->text;
    }
}
