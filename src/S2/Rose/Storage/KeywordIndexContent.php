<?php
/**
 * @copyright 2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

use S2\Rose\Entity\ExternalId;

class KeywordIndexContent
{
    protected $data = [];

    /**
     * @param ExternalId $externalId
     * @param int        $type
     *
     * @return $this
     */
    public function add(ExternalId $externalId, $type)
    {
        // Make unique. See comment in usages.
        $this->data[$externalId->toString()] = [$externalId, (int)$type];

        return $this;
    }

    public function iterate(\Closure $callback)
    {
        foreach ($this->data as $params) {
            call_user_func_array($callback, $params);
        }
    }
}
