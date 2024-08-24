<?php
/**
 * @copyright 2024 Roman Parpalak
 * @license   https://opensource.org/license/mit MIT
 */

declare(strict_types=1);

namespace S2\Rose\Entity;

class HighlightIntervals
{
    protected array $highlightIntervals = [];
    protected bool $hasPreviousInterval = false;

    public function addInterval(int $start, int $end): void
    {
        if (!$this->hasPreviousInterval) {
            $this->highlightIntervals[] = [$start, $end];
        } else {
            $this->highlightIntervals[\count($this->highlightIntervals) - 1][1] = $end;
        }

        $this->hasPreviousInterval = true;
    }

    public function skipInterval(): void
    {
        $this->hasPreviousInterval = false;
    }

    public function toArray(): array
    {
        return $this->highlightIntervals;
    }
}
