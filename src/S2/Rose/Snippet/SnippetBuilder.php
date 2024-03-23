<?php declare(strict_types=1);
/**
 * @copyright 2011-2024 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Snippet;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\Metadata\SnippetSource;
use S2\Rose\Entity\ResultSet;
use S2\Rose\Entity\Snippet;
use S2\Rose\Entity\SnippetLine;
use S2\Rose\Exception\ImmutableException;
use S2\Rose\Exception\UnknownIdException;
use S2\Rose\Helper\StringHelper;
use S2\Rose\Stemmer\IrregularWordsStemmerInterface;
use S2\Rose\Stemmer\StemmerInterface;
use S2\Rose\Storage\Dto\SnippetResult;

class SnippetBuilder
{
    protected StemmerInterface $stemmer;
    protected ?string $snippetLineSeparator;

    public function __construct(StemmerInterface $stemmer, ?string $snippetLineSeparator = null)
    {
        $this->stemmer              = $stemmer;
        $this->snippetLineSeparator = $snippetLineSeparator;
    }

    /**
     * @throws ImmutableException
     * @throws UnknownIdException
     */
    public function attachSnippets(ResultSet $result, SnippetResult $snippetResult): self
    {
        $foundWords = $result->getFoundWordPositionsByExternalId();

        $snippetResult->iterate(function (ExternalId $externalId, SnippetSource ...$snippets) use ($foundWords, $result) {
            $snippet = $this->buildSnippet(
                $foundWords[$externalId->toString()],
                $result->getHighlightTemplate(),
                $result->getRelevanceByStemsFromId($externalId),
                ...$snippets
            );
            $result->attachSnippet($externalId, $snippet);
        });

        return $this;
    }

    public function buildSnippet(array $foundPositionsByStems, string $highlightTemplate, array $relevanceByStems, SnippetSource ...$snippetSources): Snippet
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

        $introSnippetLines = array_map(
            static fn(SnippetSource $s) => SnippetLine::createFromSnippetSourceWithoutFoundWords($s),
            \array_slice($snippetSources, 0, 2)
        );

        $snippet = new Snippet($foundWordNum, $highlightTemplate, ...$introSnippetLines);

        if ($this->snippetLineSeparator !== null) {
            $snippet->setLineSeparator($this->snippetLineSeparator);
        }

        if ($foundWordNum === 0) {
            return $snippet;
        }

        $extractor = new WordsByStemsExtractor($this->stemmer, $stems);

        foreach ($snippetSources as $snippetSource) {
            [$foundWords, $foundStems] = $extractor->extract($snippetSource->getText());

            if (\count($foundWords) === 0) {
                continue;
            }

            $snippetLine = new SnippetLine(
                $snippetSource->getText(),
                $snippetSource->getFormatId(),
                array_keys($foundWords),
                array_sum(array_map(static function ($stem) use ($relevanceByStems) {
                    return $relevanceByStems[$stem] ?? 0;
                }, array_keys($foundStems)))
            );

            $snippet->attachSnippetLine($snippetSource->getMinPosition(), $snippetSource->getMaxPosition(), $snippetLine);
        }

        return $snippet;
    }
}
