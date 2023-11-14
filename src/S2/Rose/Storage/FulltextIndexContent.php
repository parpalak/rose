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

    public function add(string $word, ExternalId $externalId, array $titlePositions, array $keywordPositions, array $contentPositions, int $wordCount = 0): void
    {
        $serializedExtId = $externalId->toString();

        if (\count($contentPositions) > 0) {
            $this->dataByExternalId[$serializedExtId][$word] = $contentPositions;
        }

        // TODO refactor this data transformation
        $this->dataByWord[$word][$serializedExtId]['extId']     = $externalId;
        $this->dataByWord[$word][$serializedExtId]['wordCount'] = $wordCount;
        $this->dataByWord[$word][$serializedExtId]['tpos']      = $titlePositions;
        $this->dataByWord[$word][$serializedExtId]['kpos']      = $keywordPositions;
        $this->dataByWord[$word][$serializedExtId]['pos']       = $contentPositions;
    }

    /**
     * @return array|int[][][]
     * @deprecated TODO refactor this data transformation
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
