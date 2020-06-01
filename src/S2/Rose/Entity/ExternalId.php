<?php
/** @noinspection CallableParameterUseCaseInTypeContextInspection */

/**
 * @copyright 2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

use S2\Rose\Exception\InvalidArgumentException;

class ExternalId
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var int|null
     */
    protected $instanceId;

    /**
     * @param string   $id
     * @param int|null $instanceId
     */
    public function __construct($id, $instanceId = null)
    {
        if ($instanceId !== null) {
            if (!is_numeric($instanceId)) {
                throw new InvalidArgumentException('Instance id must be int.');
            }

            if (!($instanceId > 0)) {
                throw new InvalidArgumentException('Instance id must be positive.');
            }
        }

        if (!is_string($id) && !is_int($id) && !is_float($id)) {
            throw new InvalidArgumentException('External id must be string or int or float.');
        }

        $this->id         = $id;
        $this->instanceId = $instanceId;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->instanceId . ':' . $this->id;
    }

    public static function fromString($string)
    {
        if (!is_string($string)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects a string argument. "%s" given.',
                __METHOD__,
                is_object($string) ? get_class($string) : gettype($string)
            ));
        }
        $data = explode(':', $string, 2);

        return new static($data[1], $data[0] !== '' ? (int)$data[0] : null);
    }

    /**
     * @param ExternalId $id
     *
     * @return bool
     */
    public function equals(ExternalId $id)
    {
        return $id->toString() === $this->toString();
    }
}
