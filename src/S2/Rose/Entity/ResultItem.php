<?php
/**
 * @copyright 2016-2017 Roman Parpalak
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
	protected $relevance = 0.0;

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
	 * @param float     $relevance
	 */
	public function __construct($title, $description, \DateTime $date = null, $url, $relevance = null)
	{
		$this->title       = $title;
		$this->description = $description;
		$this->date        = $date;
		$this->url         = $url;
		$this->relevance   = $relevance;
	}

	/**
	 * @param Snippet $snippet
	 *
	 * @return $this
	 */
	public function setSnippet($snippet)
	{
		$this->snippet = $snippet;

		return $this;
	}

	/**
	 * @param float $relevance
	 *
	 * @return $this
	 */
	public function setRelevance($relevance)
	{
		$this->relevance = $relevance;

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
	public function getRelevance()
	{
		return $this->relevance;
	}

	/**
	 * @return string
	 */
	public function getSnippet()
	{
		if (!$this->snippet) {
			return $this->description;
		}

		$snippet = $this->snippet->toString(0.6);
		if ($snippet) {
			return $snippet;
		}


		return $this->description ?: $this->snippet->getTextIntroduction();
	}
}
