<?php
/**
 * Fulltext and keyword search
 *
 * @copyright 2010-2018 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose;

use S2\Rose\Entity\FulltextQuery;
use S2\Rose\Entity\FulltextResult;
use S2\Rose\Entity\Query;
use S2\Rose\Entity\ResultSet;
use S2\Rose\Exception\UnknownKeywordTypeException;
use S2\Rose\Stemmer\StemmerInterface;
use S2\Rose\Storage\StorageReadInterface;

/**
 * Class Finder
 */
class Finder
{
    const TYPE_TITLE   = 1;
    const TYPE_KEYWORD = 2;

    /**
     * @var StorageReadInterface
     */
    protected $storage;

    /**
     * @var StemmerInterface
     */
    protected $stemmer;

    /**
     * @var string
     */
    protected $highlightTemplate;

    /**
     * Finder constructor.
     *
     * @param StorageReadInterface $storage
     * @param StemmerInterface     $stemmer
     */
    public function __construct(StorageReadInterface $storage, StemmerInterface $stemmer)
    {
        $this->storage = $storage;
        $this->stemmer = $stemmer;
    }

    /**
     * @param string $highlightTemplate
     *
     * @return $this
     */
    public function setHighlightTemplate($highlightTemplate)
    {
        $this->highlightTemplate = $highlightTemplate;

        return $this;
    }

    /**
     * @param int $type
     *
     * @return int
     * @throws \S2\Rose\Exception\UnknownKeywordTypeException
     */
    protected static function getKeywordWeight($type)
    {
        if ($type == self::TYPE_KEYWORD) {
            return 30;
        }

        if ($type == self::TYPE_TITLE) {
            return 20;
        }

        throw new UnknownKeywordTypeException(sprintf('Unknown type "%s"', $type));
    }

    /**
     * Ignore frequent words encountering in indexed items.
     *
     * @param $tocSize
     *
     * @return mixed
     */
    public static function fulltextRateExcludeNum($tocSize)
    {
        return max($tocSize * 0.5, 20);
    }

    /**
     * @param array     $words
     * @param ResultSet $resultSet
     *
     * @throws \S2\Rose\Exception\ImmutableException
     */
    protected function findFulltext(array $words, ResultSet $resultSet)
    {
        $fulltextQuery        = new FulltextQuery($words, $this->stemmer);
        $fulltextIndexContent = $this->storage->fulltextResultByWords($fulltextQuery->getWordsWithStems());
        $fulltextResult       = new FulltextResult(
            $fulltextQuery,
            $fulltextIndexContent,
            $this->storage->getTocSize()
        );

        $fulltextResult->fillResultSet($resultSet);
    }

    /**
     * @param string[]  $words
     * @param ResultSet $result
     *
     * @throws \S2\Rose\Exception\ImmutableException
     */
    protected function findSimpleKeywords($words, ResultSet $result)
    {
        $wordsWithStems = $words;
        foreach ($words as $word) {
            $stem = $this->stemmer->stemWord($word);
            $wordsWithStems[] = $stem;
        }

        foreach ($this->storage->getSingleKeywordIndexByWords($wordsWithStems) as $word => $data) {
            foreach ($data as $externalId => $type) {
                $result->addWordWeight($word, $externalId, self::getKeywordWeight($type));
            }
        }
    }

    /**
     * @param string    $string
     * @param ResultSet $result
     *
     * @throws \S2\Rose\Exception\ImmutableException
     */
    protected function findSpacedKeywords($string, ResultSet $result)
    {
        foreach ($this->storage->getMultipleKeywordIndexByString($string) as $externalId => $type) {
            $result->addWordWeight($string, $externalId, self::getKeywordWeight($type));
        }
    }

    /**
     * @param Query $query
     * @param bool  $isDebug
     *
     * @return ResultSet
     * @throws \S2\Rose\Exception\ImmutableException
     */
    public function find(Query $query, $isDebug = false)
    {
        $resultSet = new ResultSet($query->getLimit(), $query->getOffset(), $isDebug);
        if ($this->highlightTemplate !== null) {
            $resultSet->setHighlightTemplate($this->highlightTemplate);
        }

        $rawWords     = $query->valueToArray();
        $cleanedQuery = implode(' ', $rawWords);
        $resultSet->addProfilePoint('Input cleanup');

        if (count($rawWords) > 1) {
            $this->findSpacedKeywords($cleanedQuery, $resultSet);
            $resultSet->addProfilePoint('Keywords with space');
        }

        if (count($rawWords) > 0) {
            $this->findSimpleKeywords($rawWords, $resultSet);
            $resultSet->addProfilePoint('Simple keywords');

            $this->findFulltext($rawWords, $resultSet);
            $resultSet->addProfilePoint('Fulltext search');
        }

        $resultSet->freeze();

        $foundExternalIds     = $resultSet->getFoundExternalIds();
        $remainingExternalIds = array_flip($foundExternalIds);

        foreach ($this->storage->getTocByExternalIds($foundExternalIds) as $externalId => $tocEntry) {
            $resultSet->attachToc($externalId, $tocEntry);
            unset($remainingExternalIds[$externalId]);
        }

        foreach ($remainingExternalIds as $externalId => $no) {
            // We found a result just before it was deleted.
            // Remove it from the result set.
            $resultSet->removeByExternalId($externalId);
        }

        return $resultSet;
    }
}
