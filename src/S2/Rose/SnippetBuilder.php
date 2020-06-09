<?php
/**
 * @copyright 2011-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose;

use S2\Rose\Entity\ExternalContent;
use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\ResultSet;
use S2\Rose\Entity\Snippet;
use S2\Rose\Entity\SnippetLine;
use S2\Rose\Exception\ImmutableException;
use S2\Rose\Exception\InvalidArgumentException;
use S2\Rose\Stemmer\IrregularWordsStemmerInterface;
use S2\Rose\Stemmer\StemmerInterface;

class SnippetBuilder
{
    const LINE_SEPARATOR = "\r";

    /**
     * @var StemmerInterface
     */
    protected $stemmer;

    /**
     * @var string
     */
    protected $snippetLineSeparator;

    /**
     * @param StemmerInterface $stemmer
     */
    public function __construct(StemmerInterface $stemmer)
    {
        $this->stemmer = $stemmer;
    }

    /**
     * @param string $snippetLineSeparator
     *
     * @return SnippetBuilder
     */
    public function setSnippetLineSeparator($snippetLineSeparator)
    {
        $this->snippetLineSeparator = $snippetLineSeparator;

        return $this;
    }

    /**
     * @param ResultSet $result
     * @param callable  $callback
     *
     * @return $this
     * @throws ImmutableException
     */
    public function attachSnippets(ResultSet $result, $callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Argument "callback" must be a callable.');
        }

        $externalIds = $result->getSortedExternalIds()->toArray();

        $extContent = $callback($externalIds);
        if (!($extContent instanceof ExternalContent)) {
            throw new InvalidArgumentException(sprintf(
                'Snippet callback must return ExternalContent object. "%s" given.',
                is_object($extContent) ? get_class($extContent) : gettype($extContent)
            ));
        }

        $result->addProfilePoint('Snippets: obtaining');

        $foundWords = $result->getFoundWordPositionsByExternalId();

        $isCleaningProfiled = false;
        $extContent->iterate(function (ExternalId $externalId, $text) use ($foundWords, $result, &$isCleaningProfiled) {
            if (!$isCleaningProfiled) {
                $isCleaningProfiled = true;
                $result->addProfilePoint('Snippets: cleaning');
            }

            $snippet = $this->buildSnippet(
                $foundWords[$externalId->toString()],
                $text,
                $result->getHighlightTemplate()
            );
            $result->attachSnippet($externalId, $snippet);
        });

        $result->addProfilePoint('Snippets: building');

        return $this;
    }

    /**
     * @param array  $foundPositionsByStems
     * @param string $content
     * @param string $highlightTemplate
     *
     * @return Snippet
     */
    public function buildSnippet($foundPositionsByStems, $content, $highlightTemplate)
    {
        // Stems of the words found in the $id chapter
        $stems        = [];
        $foundWordNum = 0;
        foreach ($foundPositionsByStems as $stem => $positions) {
            if (empty($positions)) {
                //  Not a fulltext search result (e.g. title from single keywords)
                continue;
            }
            $stems[] = $stem;
            $foundWordNum++;
        }

        // Breaking the text into lines
        $lines = explode(self::LINE_SEPARATOR, $content);

        $textStart = $lines[0] . (isset($lines[1]) ? ' ' . $lines[1] : '');
        $snippet   = new Snippet($textStart, $foundWordNum, $highlightTemplate);
        if ($this->snippetLineSeparator !== null) {
            $snippet->setLineSeparator($this->snippetLineSeparator);
        }

        if ($foundWordNum === 0) {
            return $snippet;
        }

        if ($this->stemmer instanceof IrregularWordsStemmerInterface) {
            $stems = array_merge($stems, $this->stemmer->irregularWordsFromStems($stems));
        }

        $joinedStems = implode('|', $stems);
        $joinedStems = str_replace('е', '[её]', $joinedStems);

        // Check the text for the query words
        // TODO: Make sure the modifier S works correct on cyrillic
        preg_match_all(
            '#(?<=[^\\p{L}]|^)(' . $joinedStems . ')\\p{L}*#Ssui',
            $content,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        $lineNum = 0;
        $lineEnd = strlen($lines[$lineNum]);

        $foundWordsInLines = $foundStemsInLines = [];
        foreach ($matches[0] as $i => $wordInfo) {
            $word           = $wordInfo[0];
            $stemEqualsWord = ($wordInfo[0] === $matches[1][$i][0]);
            $stemmedWord    = $this->stemmer->stemWord($word);

            // Ignore entry if the word stem differs from needed ones
            if (!$stemEqualsWord && !in_array($stemmedWord, $stems, true)) {
                continue;
            }

            $offset = $wordInfo[1];

            while ($lineEnd < $offset && isset($lines[$lineNum + 1])) {
                $lineNum++;
                $lineEnd += 1 + strlen($lines[$lineNum]);
            }

            $foundStemsInLines[$lineNum][$stemmedWord] = 1;
            $foundWordsInLines[$lineNum][$word] = 1;
        }

        foreach ($foundStemsInLines as $lineIndex => $foundStemsInLine) {
            $snippetLine = new SnippetLine(
                $lines[$lineIndex],
                array_keys($foundWordsInLines[$lineIndex]),
                count($foundStemsInLine)
            );
            $snippet->attachSnippetLine($lineIndex, $snippetLine);
        }

        return $snippet;
    }
}
