<?php
/**
 * @copyright 2016-2017 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

use S2\Rose\Exception\RuntimeException;
use S2\Rose\Stemmer\StemmerInterface;

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
	 * @var string
	 */
	protected $highlightTemplate;

	/**
	 * @var string[]
	 */
	protected $foundWords = array();

	/**
	 * ResultItem constructor.
	 *
	 * @param string    $title
	 * @param string    $description
	 * @param \DateTime $date
	 * @param string    $url
	 * @param string    $highlightTemplate
	 */
	public function __construct(
		$title,
		$description,
		\DateTime $date = null,
		$url,
		$highlightTemplate
	) {
		$this->title             = $title;
		$this->description       = $description;
		$this->date              = $date;
		$this->url               = $url;
		$this->highlightTemplate = $highlightTemplate;
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

	/**
	 * @param string[] $words
	 *
	 * @return $this
	 */
	public function setFoundWords(array $words)
	{
		$this->foundWords = $words;

		return $this;
	}

	/**
	 * TODO Refactor the highlight logic to a separate class.
	 *
	 * @param StemmerInterface $stemmer
	 *
	 * @return string
	 */
	public function getHighlightedTitle(StemmerInterface $stemmer)
	{
		$template = $this->highlightTemplate;

		if (strpos($template, '%s') === false) {
			throw new RuntimeException('Highlight template must contain "%s" substring for sprintf() function.');
		}

		$joinedStems = implode('|', $this->foundWords);
		$joinedStems = str_replace('е', '[её]', $joinedStems);

		$replacedLine = preg_replace_callback(
			'#(?<=[^a-zа-я]|^)(' . $joinedStems . ')[a-zа-я]*#Ssui',
			function ($matches) use ($template, $stemmer) {
				$word        = $matches[0];
				$stem        = str_replace('ё', 'е', mb_strtolower($matches[1]));
				$stemmedWord = $stemmer->stemWord($word);

				if ($stem != $stemmedWord) {
					return $word;
				}

				return sprintf($template, $word);
			},
			$this->title
		);

		return $replacedLine;
	}
}
