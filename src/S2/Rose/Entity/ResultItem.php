<?php
/**
 * @copyright 2016-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

use S2\Rose\Exception\InvalidArgumentException;
use S2\Rose\Stemmer\IrregularWordsStemmerInterface;
use S2\Rose\Stemmer\StemmerInterface;

class ResultItem
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
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var \DateTime|null
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
    protected $foundWords = [];

    /**
     * @param string    $id Id in external system
     * @param int|null  $instanceId
     * @param string    $title
     * @param string    $description
     * @param \DateTime $date
     * @param string    $url
     * @param string    $highlightTemplate
     */
    public function __construct(
        $id,
        $instanceId,
        $title,
        $description,
        \DateTime $date = null,
        $url,
        $highlightTemplate
    ) {
        $this->id                = $id;
        $this->instanceId        = $instanceId;
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
            throw new InvalidArgumentException('Highlight template must contain "%s" substring for sprintf() function.');
        }

        $joinedStems = $this->foundWords;
        if ($stemmer instanceof IrregularWordsStemmerInterface) {
            $joinedStems = array_merge($joinedStems, $stemmer->irregularWordsFromStems($this->foundWords));
        }
        $joinedStems = implode('|', $joinedStems);
        $joinedStems = str_replace('е', '[её]', $joinedStems);

        $replacedLine = preg_replace_callback(
            '#(?<=[^\\p{L}]|^)(' . $joinedStems . ')\\p{L}*#Ssui',
            function ($matches) use ($template, $stemmer) {
                $word        = $matches[0];
                $stemmedWord = $stemmer->stemWord($word);

                if (!in_array($stemmedWord, $this->foundWords, true)) {
                    return $word;
                }

                return sprintf($template, $word);
            },
            $this->title
        );

        return $replacedLine;
    }
}
