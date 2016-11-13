<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

/**
 * Class ResultItem
 */
class ResultItem
{
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
	 * @var float
	 */
	protected $relevancy = 0.0;

	/**
	 * @var Snippet
	 */
	protected $snippet;

	/**
	 * ResultItem constructor.
	 *
	 * @param string    $title
	 * @param string    $description
	 * @param \DateTime $date
	 * @param string    $url
	 * @param float     $relevancy
	 */
	public function __construct($title, $description, \DateTime $date = null, $url, $relevancy)
	{
		$this->title       = $title;
		$this->description = $description;
		$this->date        = $date;
		$this->url         = $url;
		$this->relevancy   = $relevancy;
	}

	/**
	 * @param Snippet $snippet
	 */
	public function setSnippet($snippet)
	{
		$this->snippet = $snippet;
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
	 * @return float
	 */
	public function getRelevancy()
	{
		return $this->relevancy;
	}

	/**
	 * @return Snippet
	 */
	public function getSnippet()
	{
		if (!$this->snippet) {
			return $this->description;
		}

		if ($this->snippet->getRelevance() > 0.6) {
			return $this->snippet->getSnippet();
		}

		return $this->description ?: $this->snippet->getTextIntroduction();
	}
}
