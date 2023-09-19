<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity\Metadata;

use S2\Rose\Exception\InvalidArgumentException;

class SnippetSource
{
    public const  FORMAT_PLAIN_TEXT = 0;
    public const  FORMAT_INTERNAL   = 1;
    private const ALLOWED_FORMATS   = [self::FORMAT_PLAIN_TEXT, self::FORMAT_INTERNAL];

    private string $text;
    private int $minPosition;
    private int $maxPosition;
    private int $formatId;

    public function __construct(string $text, int $formatId, int $minPosition, int $maxPosition)
    {
        if ($minPosition < 0) {
            throw new InvalidArgumentException('Word position cannot be negative.');
        }
        if ($minPosition > $maxPosition) {
            throw new InvalidArgumentException('Minimal word position cannot be greater than maximal.');
        }

        if (!\in_array($formatId, self::ALLOWED_FORMATS)) {
            throw new InvalidArgumentException(sprintf('Unknown snippet format "%s".', $formatId));
        }

        $this->text        = $text;
        $this->minPosition = $minPosition;
        $this->maxPosition = $maxPosition;
        $this->formatId    = $formatId;
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

    public function getFormatId(): int
    {
        return $this->formatId;
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
