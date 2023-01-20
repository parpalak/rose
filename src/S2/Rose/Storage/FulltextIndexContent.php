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

    public function add(string $word, ExternalId $externalId, array $positions, int $wordCount = 0): void
    {
        $serializedExtId = $externalId->toString();

        $this->dataByExternalId[$serializedExtId][$word] = $positions;

        // TODO refactor this data transformation
        $this->dataByWord[$word][$serializedExtId]['extId']     = $externalId;
        $this->dataByWord[$word][$serializedExtId]['wordCount'] = $wordCount;
        $this->dataByWord[$word][$serializedExtId]['pos']       = $positions;
    }

    /**
     * @return array|int[][][]
     * @deprecated TODO refactor this data transformation
     */
    public function toArray(): array
    {
        return $this->dataByWord;
    }

    public function iterateWordPositions(\Closure $callback): void
    {
        foreach ($this->dataByExternalId as $serializedExtId => $data) {
            $callback(ExternalId::fromString($serializedExtId), new WordPositionContainer($data));
        }
    }
}
