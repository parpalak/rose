<?php
/**
 * @copyright 2017-2018 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

use S2\Rose\Entity\WordPositionContainer;

/**
 * Class FulltextResultMock
 */
class FulltextIndexContent
{
    /**
     * @var array
     */
    protected $dataByWord = [];

    /**
     * @var array
     */
    protected $dataByExternalId = [];

    /**
     * @param string $word
     * @param string $externalId
     * @param int    $position
     */
    public function add($word, $externalId, $position)
    {
        $this->dataByWord[$word][$externalId][]       = $position;
        $this->dataByExternalId[$externalId][$word][] = $position;
    }

    /**
     * @return array|int[][][]
     */
    public function toArray()
    {
        return $this->dataByWord;
    }

    /**
     * @return array|WordPositionContainer[]
     */
    public function toWordPositionContainerArray()
    {
        $result = [];
        foreach ($this->dataByExternalId as $externalId => $data) {
            $result[$externalId] = new WordPositionContainer($data);
        }

        return $result;
    }

}
