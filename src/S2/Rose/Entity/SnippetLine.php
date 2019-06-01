<?php
/**
 * @copyright 2017-2018 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

use S2\Rose\Exception\RuntimeException;

class SnippetLine
{
    const STORE_MARKER = "\r";

    /**
     * @var string[]
     */
    protected $foundWords = [];

    /**
     * @var string
     */
    protected $line = '';

    /**
     * @var int
     */
    protected $foundStemCount = 0;

    /**
     * @var string|null
     */
    protected $lineWithoutEntities;

    /**
     * @var string[]
     */
    protected $storedEntities;

    /**
     * @param string   $line
     * @param string[] $foundWords
     * @param int      $foundStemCount
     */
    public function __construct($line, array $foundWords, $foundStemCount)
    {
        $this->line           = $line;
        $this->foundWords     = $foundWords;
        $this->foundStemCount = $foundStemCount;
    }

    /**
     * @return int
     */
    public function getStemCount()
    {
        return $this->foundStemCount;
    }

    /**
     * @return string[]
     */
    public function getFoundWords()
    {
        return $this->foundWords;
    }

    /**
     * @param string $highlightTemplate
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function getHighlighted($highlightTemplate)
    {
        if (strpos($highlightTemplate, '%s') === false) {
            throw new RuntimeException('Highlight template must contain "%s" substring for sprintf() function.');
        }

        $line = $this->getLineWithoutEntities();

        $replacedLine = preg_replace_callback(
            '#\b(' . implode('|', $this->foundWords) . ')\b#su',
            function ($matches) use ($highlightTemplate) {
                return sprintf($highlightTemplate, $matches[1]);
            },
            $line,
            -1,
            $count
        );

        return $this->restoreEntities($replacedLine);
    }

    /**
     * @return string
     */
    protected function getLineWithoutEntities()
    {
        if ($this->lineWithoutEntities !== null) {
            return $this->lineWithoutEntities;
        }

        // Remove substrings that are not store markers
        $this->lineWithoutEntities = str_replace(self::STORE_MARKER, '', $this->line);

        $storedEntities = [];
        $storeMarker    = self::STORE_MARKER;

        $this->lineWithoutEntities = preg_replace_callback(
            '#&(\\#[1-9]\d{1,3}|[A-Za-z][0-9A-Za-z]+);#',
            function (array $matches) use (&$storedEntities, $storeMarker) {
                $storedEntities[] = $matches[0];

                return $storeMarker;
            },
            $this->line
        );

        $this->storedEntities = $storedEntities;

        return $this->lineWithoutEntities;
    }

    /**
     * @param string $line
     *
     * @return string
     */
    protected function restoreEntities($line)
    {
        $i = 0;
        while (true) {
            $pos = strpos($line, self::STORE_MARKER);
            if ($pos === false || !isset($this->storedEntities[$i])) {
                break;
            }

            $line = substr_replace($line, $this->storedEntities[$i], $pos, strlen(self::STORE_MARKER));
            $i++;
        }

        return $line;
    }
}
