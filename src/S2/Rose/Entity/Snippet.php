<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

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
	protected $textIntroduction = '';

	/**
	 * @var float
	 */
	protected $relevance = 0.0;

	/**
	 * Snippet constructor.
	 *
	 * @param string $value
	 * @param string $textIntroduction
	 * @param float  $relevance
	 */
	public function __construct($value, $textIntroduction, $relevance)
	{
		$this->snippet          = $value;
		$this->textIntroduction = $textIntroduction;
		$this->relevance        = $relevance;
	}

	/**
	 * @return string
	 */
	public function getSnippet()
	{
		return $this->snippet;
	}

	/**
	 * @return string
	 */
	public function getTextIntroduction()
	{
		return $this->textIntroduction;
	}

	/**
	 * @return float
	 */
	public function getRelevance()
	{
		return $this->relevance;
	}
}
