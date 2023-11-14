<?php declare(strict_types=1);
/**
 * @copyright 2017-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\WordPositionContainer;

class FulltextIndexContent
{
    protected array $dataByWord = [];
    protected array $dataByExternalId = [];

    public function add(string $word, FulltextIndexPositionBag $positionBag): void
    {
        $serializedExtId = $positionBag->getExternalId()->toString();

        $contentPositions = $positionBag->getContentPositions();
        if (\count($contentPositions) > 0) {
            $this->dataByExternalId[$serializedExtId][$word] = $contentPositions;
        }

        $this->dataByWord[$word][$serializedExtId] = $positionBag;
    }

    /**
     * @return FulltextIndexPositionBag[][]
     * @deprecated TODO rename or refactor this data transformation
     */
    public function toArray(): array
    {
        return $this->dataByWord;
    }

    public function iterateContentWordPositions(\Closure $callback): void
    {
        foreach ($this->dataByExternalId as $serializedExtId => $data) {
            $callback(ExternalId::fromString($serializedExtId), new WordPositionContainer($data));
        }
    }
}
