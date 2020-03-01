<?php
/**
 * @copyright 2017-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\WordPositionContainer;

class FulltextIndexContent
{
    protected $dataByWord = [];
    protected $dataByExternalId = [];

    /**
     * @param string     $word
     * @param ExternalId $externalId
     * @param int        $position
     */
    public function add($word, ExternalId $externalId, $position)
    {
        $serializedExtId = $externalId->toString();

        $this->dataByExternalId[$serializedExtId][$word][] = $position;

        // TODO refactor this data transformation
        $this->dataByWord[$word][$serializedExtId]['extId'] = $externalId;
        $this->dataByWord[$word][$serializedExtId]['pos'][] = $position;
    }

    /**
     * @return array|int[][][]
     * @deprecated TODO refactor this data transformation
     */
    public function toArray()
    {
        return $this->dataByWord;
    }

    public function iterateWordPositions(\Closure $callback)
    {
        foreach ($this->dataByExternalId as $serializedExtId => $data) {
            $callback(ExternalId::fromString($serializedExtId), new WordPositionContainer($data));
        }
    }
}
