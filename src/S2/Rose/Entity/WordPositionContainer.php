<?php
/**
 * @copyright 2017-2018 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

/**
 * Class WordPositionContainer
 */
class WordPositionContainer
{
    const INFINITY = 100000000;
    /**
     * @var array
     */
    protected $data = [];

    /**
     * WordPositionContainer constructor.
     *
     * Accepts data in the following form:
     * [
     *     'word1' => [23, 56, 74],
     *     'word2' => [2, 57],
     * ]
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @param string $word
     * @param int    $position
     *
     * @return $this
     */
    public function addWordAt($word, $position)
    {
        $this->data[$word][] = $position;

        return $this;
    }

    /**
     * @param WordPositionContainer $referenceContainer
     *
     * @return array
     */
    public function compareWith(self $referenceContainer)
    {
        $wordMap = array_keys($this->data);
        $len     = count($wordMap);

        $result = [];
        for ($i = 0; $i < $len; $i++) {
            $word1 = $wordMap[$i];
            for ($j = $i + 1; $j < $len; $j++) {
                $word2 = $wordMap[$j];

                $referenceDistance = $referenceContainer->getClosestDistanceBetween($word1, $word2, 0);
                if ($referenceDistance == self::INFINITY) {
                    continue;
                }

                $distance = $this->getClosestDistanceBetween($word1, $word2, $referenceDistance);

                $result[] = [$word1, $word2, $distance];
            }
        }

        return $result;
    }

    /**
     * @param number[] $a1
     * @param number[] $a2
     * @param int      $shift
     *
     * @return number
     */
    protected static function compareArrays(array $a1, array $a2, $shift)
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

    /**
     * @param string $word1
     * @param string $word2
     * @param int    $shift
     *
     * @return number
     */
    public function getClosestDistanceBetween($word1, $word2, $shift = 0)
    {
        if (!isset($this->data[$word1], $this->data[$word2])) {
            return self::INFINITY;
        }

        return self::compareArrays($this->data[$word1], $this->data[$word2], $shift);
    }
}
