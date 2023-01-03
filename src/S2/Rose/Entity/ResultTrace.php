<?php
/**
 * @copyright 2017-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

class ResultTrace
{
    protected $data = [];

    /**
     * @param string        $word
     * @param string        $serializedExtId
     * @param float[]|array $weights
     * @param int[]         $positions
     */
    public function addWordWeight($word, $serializedExtId, array $weights, $positions)
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
     * @param string        $word
     * @param string        $serializedExtId
     * @param float[]|array $weights
     */
    public function addKeywordWeight($word, $serializedExtId, array $weights)
    {
        $this->data[$serializedExtId]['keyword ' . $word][] = [
            (string)array_product($weights) => $weights,
        ];
    }

    /**
     * @param string $word1
     * @param string $word2
     * @param string $serializedExtId
     * @param float  $weight
     * @param int    $distance
     */
    public function addNeighbourWeight($word1, $word2, $serializedExtId, $weight, $distance)
    {
        $this->data[$serializedExtId]['fulltext ' . $word1 . ' - ' . $word2][] = sprintf('%s: matches are close (shift = %s)', $weight, $distance);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }
}
