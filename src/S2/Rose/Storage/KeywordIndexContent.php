<?php declare(strict_types=1);
/**
 * @copyright 2020-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Finder;

class KeywordIndexContent
{
    protected array $data = [];

    public function add(ExternalId $externalId, int $type, ?int $tocSize = null, ?int $foundTocEntriesNum = null): KeywordIndexContent
    {
        // Make unique (see comment in usages).
        // Title is more important than usual keywords.

        if ($type === Finder::TYPE_TITLE) {
            // Overwrite with high priority
            unset($this->data[$externalId->toString() . Finder::TYPE_KEYWORD]);
        } elseif (isset($this->data[$externalId->toString() . Finder::TYPE_TITLE])) {
            // Do not overwrite with low priority
            return $this;
        }

        $this->data[$externalId->toString() . $type] = [$externalId, $type, $tocSize, $foundTocEntriesNum];

        return $this;
    }

    public function iterate(\Closure $callback): void
    {
        foreach ($this->data as $params) {
            $callback(...$params);
        }
    }
}
