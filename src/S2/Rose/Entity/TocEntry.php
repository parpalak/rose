<?php
/**
 * @copyright 2016-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

class TocEntry
{
    /**
     * @var
     */
    protected $internalId;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var string
     */
    protected $url = '';

    /**
     * @var string
     */
    protected $hash;

    /**
     * @param string    $title
     * @param string    $description
     * @param \DateTime $date
     * @param string    $url
     * @param string    $hash
     */
    public function __construct($title, $description, \DateTime $date = null, $url, $hash)
    {
        $this->title       = $title;
        $this->description = $description;
        $this->date        = $date;
        $this->url         = $url;
        $this->hash        = $hash;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getInternalId()
    {
        return $this->internalId;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param mixed $internalId TODO why mixed? Int?
     *
     * @return TocEntry
     * @deprecated Make immutable
     */
    public function setInternalId($internalId)
    {
        $this->internalId = $internalId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFormattedDate()
    {
        return $this->date !== null ? $this->date->format('Y-m-d H:i:s') : null;
    }
}
