<?php declare(strict_types=1);
/**
 * @copyright 2017-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

class WordPositionContainer
{
    private const INFINITY = 100000000;
    protected array $data = [];

    /**
     * Accepts data in the following form:
     * [
     *     'word1' => [23, 56, 74],
     *     'word2' => [2, 57],
     * ]
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function addWordAt(string $word, int $position): self
    {
        $this->data[$word][] = $position;

        sort($this->data[$word]); // TODO make more reliable requirement of input arrays to be sorted.

        return $this;
    }

    public function compareWith(self $referenceContainer): array
    {
        $wordMap = array_keys($this->data);
        $len     = \count($wordMap);

        $result = [];
        /** @noinspection ForeachInvariantsInspection */
        for ($i = 0; $i < $len; $i++) {
            $word1 = (string)$wordMap[$i];
            for ($j = $i + 1; $j < $len; $j++) {
                $word2 = (string)$wordMap[$j];

                $referenceDistance = $referenceContainer->getClosestDistanceBetween($word1, $word2, 0);
                if ($referenceDistance === self::INFINITY) {
                    continue;
                }

                $distance = $this->getClosestDistanceBetween($word1, $word2, $referenceDistance);

                $result[] = [$word1, $word2, $distance];
            }
        }

        return $result;
    }

    /**
     * This method uses linear algorithm, therefore input arrays must be sorted.
     * Otherwise, the output is incorrect.
     *
     * @param int[] $a1
     * @param int[] $a2
     *
     * @return int It's important to return a signed value, not an absolute value.
     */
    protected static function compareArrays(array $a1, array $a2, int $shift): int
    {
        $len1 = \count($a1);
        $len2 = \count($a2);

        $result = self::INFINITY;
        $index1 = 0;
        $index2 = 0;

        while ($index1 < $len1 && $index2 < $len2) {
            $diff = $a2[$index2] - $a1[$index1] - $shift;

            if ($diff === 0) {
                return 0;
            }

            if (abs($result) > abs($diff)) {
                $result = $diff;
            }

            if ($diff < 0) {
                $index2++;
            } else {
                $index1++;
            }
        }

        return $result;
    }

    public function getClosestDistanceBetween(string $word1, string $word2, int $shift = 0): int
    {
        if (!isset($this->data[$word1], $this->data[$word2])) {
            return self::INFINITY;
        }

        return self::compareArrays($this->data[$word1], $this->data[$word2], $shift);
    }
}
