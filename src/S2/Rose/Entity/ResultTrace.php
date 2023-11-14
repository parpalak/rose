<?php
/**
 * @copyright 2017-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

class ResultTrace
{
    protected array $data = [];

    /**
     * @param float[]|array $weights
     * @param int[]         $positions
     */
    public function addWordWeight(string $word, string $serializedExtId, array $weights, array $positions): void
    {
        $this->data[$serializedExtId]['fulltext ' . $word][] = [
            sprintf(
                '%s: match at positions [%s]',
                array_product($weights),
                implode(', ', $positions)
            ) => $weights,
        ];
    }

    /**
     * @param float[]|array $weights
     */
    public function addKeywordWeight(string $word, string $serializedExtId, array $weights): void
    {
        $this->data[$serializedExtId]['keyword ' . $word][] = [
            (string)array_product($weights) => $weights,
        ];
    }

    public function addNeighbourWeight(string $word1, string $word2, string $serializedExtId, float $weight, int $distance): void
    {
        $this->data[$serializedExtId]['fulltext ' . $word1 . ' - ' . $word2][] = $weight . ': matches are close (shift = ' . $distance . ')';
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
