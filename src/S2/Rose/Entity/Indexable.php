<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

/**
 * Class IndexItem
 */
class Indexable
{
	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $title = '';

	/**
	 * @var string
	 */
	protected $content = '';

	/**
	 * @var string
	 */
	protected $keywords = '';

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
	 * Chapter constructor.
	 *
	 * @param string $id
	 * @param string $title
	 * @param string $content
	 */
	public function __construct($id, $title, $content)
	{
		$this->id      = $id;
		$this->title   = $title;
		$this->content = $content;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $id
	 *
	 * @return Indexable
	 */
	public function setId($id)
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 *
	 * @return Indexable
	 */
	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @param string $content
	 *
	 * @return Indexable
	 */
	public function setContent($content)
	{
		$this->content = $content;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getKeywords()
	{
		return $this->keywords;
	}

	/**
	 * @param string $keywords
	 *
	 * @return Indexable
	 */
	public function setKeywords($keywords)
	{
		$this->keywords = $keywords;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 *
	 * @return Indexable
	 */
	public function setDescription($description)
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @param \DateTime $date
	 *
	 * @return Indexable
	 */
	public function setDate(\DateTime $date = null)
	{
		$this->date = $date;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @param string $url
	 *
	 * @return Indexable
	 */
	public function setUrl($url)
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * @return TocEntry
	 */
	public function toTocEntry()
	{
		return new TocEntry($this->getTitle(), $this->getDescription(), $this->getDate(), $this->getUrl(), $this->calcHash());
	}

	/**
	 * @return string
	 */
	public function calcHash()
	{
		return md5(serialize(array(
			$this->getTitle(),
			$this->getDescription(),
			$this->getKeywords(),
			$this->getContent()
		)));
	}
}
