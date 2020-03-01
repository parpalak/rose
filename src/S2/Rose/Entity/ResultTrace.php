<?php
/**
 * @copyright 2017-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

class ResultTrace
{
    protected $data = [];

    /**
     * @param string $word
     * @param string $serializedExtId
     * @param float  $weight
     * @param int[]  $positions
     */
    public function addWordWeight($word, $serializedExtId, $weight, $positions)
    {
        $this->data[$serializedExtId]['fulltext ' . $word][] = sprintf('%s: match at positions [%s]', $weight, implode(', ', $positions));
    }

    /**
     * @param string $word
     * @param string $serializedExtId
     * @param float  $weight
     */
    public function addKeywordWeight($word, $serializedExtId, $weight)
    {
        $this->data[$serializedExtId]['keyword ' . $word][] = sprintf('%s', $weight);
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
