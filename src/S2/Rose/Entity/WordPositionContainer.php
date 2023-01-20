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

        return $this;
    }

    public function compareWith(self $referenceContainer): array
    {
        $wordMap = array_keys($this->data);
        $len     = count($wordMap);

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
     * @param int[] $a1
     * @param int[] $a2
     */
    protected static function compareArrays(array $a1, array $a2, int $shift): int
    {
        $result = self::INFINITY;
        foreach ($a1 as $x) {
            foreach ($a2 as $y) {
                if (abs($y - $x - $shift) < abs($result)) {
                    $result = $y - $x - $shift;
                }
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
