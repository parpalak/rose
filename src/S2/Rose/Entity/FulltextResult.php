<?php
/**
 * @copyright 2017-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

use S2\Rose\Exception\ImmutableException;
use S2\Rose\Storage\FulltextIndexContent;

class FulltextResult
{
    /**
     * @var int
     */
    protected $tocSize = 0;

    /**
     * @var FulltextQuery
     */
    protected $query;

    /**
     * @var FulltextIndexContent
     */
    protected $fulltextIndexContent;

    /**
     * @param FulltextQuery        $query
     * @param FulltextIndexContent $fulltextIndexContent
     * @param int                  $tocSize
     */
    public function __construct(FulltextQuery $query, FulltextIndexContent $fulltextIndexContent, $tocSize = 0)
    {
        $this->query                = $query;
        $this->fulltextIndexContent = $fulltextIndexContent;
        $this->tocSize              = $tocSize;
    }

    /**
     * https://i.upmath.me/svg/%5Cbegin%7Btikzpicture%7D%5Bscale%3D1.0544%5D%5Csmall%0A%5Cbegin%7Baxis%7D%5Baxis%20line%20style%3Dgray%2C%0A%09samples%3D100%2C%0A%09xmin%3D-1.2%2C%20xmax%3D1.2%2C%0A%09ymin%3D0%2C%20ymax%3D1.1%2C%0A%09restrict%20y%20to%20domain%3D-0.1%3A1%2C%0A%09ytick%3D%7B1%7D%2C%0A%09xtick%3D%7B-1%2C1%7D%2C%0A%09axis%20equal%2C%0A%09axis%20x%20line%3Dcenter%2C%0A%09axis%20y%20line%3Dcenter%2C%0A%09xlabel%3D%24x%24%2Cylabel%3D%24y%24%5D%0A%5Caddplot%5Bred%2Cdomain%3D-2%3A1%2Csemithick%5D%7Bexp(-(x%2F0.38)%5E2)%7D%3B%0A%5Caddplot%5Bred%5D%20coordinates%20%7B(0.8%2C0.6)%7D%20node%7B%24y%3De%5E%7B-%5Cleft(x%2F0.38%5Cright)%5E2%7D%24%7D%3B%0A%5Cpath%20(axis%20cs%3A0%2C0)%20node%20%5Banchor%3Dnorth%20west%2Cyshift%3D-0.07cm%5D%20%7B0%7D%3B%0A%5Cend%7Baxis%7D%0A%5Cend%7Btikzpicture%7D
     *
     * @param int $tocSize
     * @param int $foundTocEntriesNum
     *
     * @return float
     */
    public static function frequencyReduction($tocSize, $foundTocEntriesNum)
    {
        if ($tocSize < 5) {
            return 1;
        }

        return exp(-(($foundTocEntriesNum / $tocSize) / 0.38) ** 2);
    }

    /**
     * Weight ratio for repeating words in the indexed item.
     *
     * @param int $repeatNum
     *
     * @return float
     */
    protected static function repeatWeightRatio($repeatNum)
    {
        return min(0.5 * ($repeatNum - 1) + 1, 2);
    }

    /**
     * Weight ratio for a pair of words. Accepts the difference of distances
     * in the indexed item and the search query.
     *
     * @param float $distance
     *
     * @return float
     */
    protected static function neighbourWeight($distance)
    {
        return 20.0 / (1 + pow($distance / 5.0, 2));
    }

    /**
     * @param float $ratio
     * @param int   $querySize
     * @param int   $wordInTocNum
     *
     * @return float
     */
    protected static function fulltextWeight($ratio, $querySize, $wordInTocNum)
    {
        return $ratio * self::repeatWeightRatio($wordInTocNum);
    }

    /**
     * @param ResultSet $resultSet
     *
     * @throws ImmutableException
     */
    public function fillResultSet(ResultSet $resultSet)
    {
        $queryWordCount = $this->query->getCount();

        foreach ($this->fulltextIndexContent->toArray() as $word => $items) {
            $reductionRatio = self::frequencyReduction($this->tocSize, count($items));

            foreach ($items as $positions) {
                $weight = self::fulltextWeight($reductionRatio, $queryWordCount, count($positions['pos']));
                $resultSet->addWordWeight($word, $positions['extId'], $weight, $positions['pos']);
            }
        }

        $referenceContainer = $this->query->toWordPositionContainer();

        $this->fulltextIndexContent->iterateWordPositions(
            static function (ExternalId $id, WordPositionContainer $container) use ($referenceContainer, $resultSet) {
                $pairsDistance = $container->compareWith($referenceContainer);
                foreach ($pairsDistance as $pairDistance) {
                    list($word1, $word2, $distance) = $pairDistance;
                    $weight = self::neighbourWeight($distance);
                    $resultSet->addNeighbourWeight($word1, $word2, $id, $weight, $distance);
                }
            }
        );
    }
}
