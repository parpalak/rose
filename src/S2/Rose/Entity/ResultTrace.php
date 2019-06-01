<?php
/**
 * @copyright 2017 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

/**
 * Class ResultTrace
 */
class ResultTrace
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param string $word
     * @param string $externalId
     * @param float  $weight
     * @param int[]  $positions
     */
    public function addWordWeight($word, $externalId, $weight, $positions)
    {
        $this->data[$externalId]['fulltext ' . $word][] = sprintf('%s: match at positions [%s]', $weight, implode(', ', $positions));
    }

    /**
     * @param string $word
     * @param string $externalId
     * @param float  $weight
     */
    public function addKeywordWeight($word, $externalId, $weight)
    {
        $this->data[$externalId]['keyword ' . $word][] = sprintf('%s', $weight);
    }

    /**
     * @param string $word1
     * @param string $word2
     * @param string $externalId
     * @param float  $weight
     * @param int    $distance
     */
    public function addNeighbourWeight($word1, $word2, $externalId, $weight, $distance)
    {
        $this->data[$externalId]['fulltext ' . $word1 . ' - ' . $word2][] = sprintf('%s: matches are close (shift = %s)', $weight, $distance);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }
}
