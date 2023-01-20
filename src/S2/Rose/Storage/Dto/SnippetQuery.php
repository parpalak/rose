<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage\Dto;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\ExternalIdCollection;
use S2\Rose\Exception\LogicException;

class SnippetQuery
{
    private array $data = [];

    public function __construct(ExternalIdCollection $externalIds)
    {
        foreach ($externalIds->toArray() as $externalId) {
            $this->data[$externalId->toString()] = null;
        }
    }

    /**
     * @param int[] $positions
     */
    public function attach(ExternalId $externalId, array $positions): void
    {
        $serializedExtId = $externalId->toString();
        if (isset($this->data[$serializedExtId])) {
            throw new LogicException(sprintf('SnippetQuery already has id "%s".', $serializedExtId));
        }
        $this->data[$serializedExtId] = $positions;
    }

    public function iterate(callable $callback): void
    {
        foreach ($this->data as $serializedExtId => $positions) {
            $callback(ExternalId::fromString($serializedExtId), $positions);
        }
    }

    /**
     * @return ExternalId[]
     */
    public function getExternalIds(): array
    {
        return array_map(static fn(string $serializedExtId) => ExternalId::fromString($serializedExtId), array_keys($this->data));
    }
}
