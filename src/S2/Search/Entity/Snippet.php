<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Search\Entity;

/**
 * Class Snippet
 */
class Snippet
{
	/**
	 * @var string
	 */
	protected $snippet = '';

	/**
	 * @var string
	 */
	protected $textStart = '';

	/**
	 * @var float
	 */
	protected $relevance = 0.0;

	/**
	 * @var string
	 */
	protected $description = '';

	/**
	 * Snippet constructor.
	 *
	 * @param string $snippet
	 * @param string $textStart
	 * @param float  $relevance
	 */
	public function __construct($snippet, $textStart, $relevance)
	{
		$this->snippet   = $snippet;
		$this->textStart = $textStart;
		$this->relevance = $relevance;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getValue()
	{
		if ($this->relevance > 0.6) {
			return $this->snippet;
		}

		return $this->description ?: $this->textStart;
	}
}
