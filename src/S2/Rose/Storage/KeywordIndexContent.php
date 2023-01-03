<?php
/**
 * @copyright 2020-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Finder;

class KeywordIndexContent
{
    protected $data = [];

    /**
     * @param ExternalId $externalId
     * @param int        $type
     * @param int|null   $tocSize
     * @param int|null   $foundTocEntriesNum
     *
     * @return self
     */
    public function add(ExternalId $externalId, $type, $tocSize = null, $foundTocEntriesNum = null)
    {
        $type = (int)$type;

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

    public function iterate(\Closure $callback)
    {
        foreach ($this->data as $params) {
            call_user_func_array($callback, $params);
        }
    }
}
