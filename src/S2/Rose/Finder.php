<?php
/**
 * Fulltext and keyword search
 *
 * @copyright 2010-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\FulltextQuery;
use S2\Rose\Entity\FulltextResult;
use S2\Rose\Entity\Query;
use S2\Rose\Entity\ResultSet;
use S2\Rose\Exception\ImmutableException;
use S2\Rose\Exception\UnknownKeywordTypeException;
use S2\Rose\Stemmer\StemmerInterface;
use S2\Rose\Storage\StorageReadInterface;

class Finder
{
    const TYPE_TITLE = 1;
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
     * @throws UnknownKeywordTypeException
     */
    protected static function getKeywordWeight($type)
    {
        if ($type === self::TYPE_KEYWORD) {
            return 30;
        }

        if ($type === self::TYPE_TITLE) {
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
     * @param int|null  $instanceId
     * @param ResultSet $resultSet
     *
     * @throws ImmutableException
     */
    protected function findFulltext(array $words, $instanceId, ResultSet $resultSet)
    {
        $fulltextQuery        = new FulltextQuery($words, $this->stemmer);
        $fulltextIndexContent = $this->storage->fulltextResultByWords($fulltextQuery->getWordsWithStems(), $instanceId);
        $fulltextResult       = new FulltextResult(
            $fulltextQuery,
            $fulltextIndexContent,
            $this->storage->getTocSize($instanceId)
        );

        $fulltextResult->fillResultSet($resultSet);
    }

    /**
     * @param string[]  $words
     * @param int|null  $instanceId
     * @param ResultSet $result
     */
    protected function findSimpleKeywords($words, $instanceId, ResultSet $result)
    {
        $wordsWithStems = $words;
        foreach ($words as $word) {
            $stem             = $this->stemmer->stemWord($word);
            $wordsWithStems[] = $stem;
        }

        foreach ($this->storage->getSingleKeywordIndexByWords($wordsWithStems, $instanceId) as $word => $content) {
            $content->iterate(static function (ExternalId $externalId, $type) use ($word, $result) {
                $result->addWordWeight($word, $externalId, self::getKeywordWeight($type));
            });
        }
    }

    /**
     * @param string    $string
     * @param int|null  $instanceId
     * @param ResultSet $result
     */
    protected function findSpacedKeywords($string, $instanceId, ResultSet $result)
    {
        $content = $this->storage->getMultipleKeywordIndexByString($string, $instanceId);
        $content->iterate(static function (ExternalId $externalId, $type) use ($string, $result) {
            $result->addWordWeight($string, $externalId, self::getKeywordWeight($type));
        });
    }

    /**
     * @param Query $query
     * @param bool  $isDebug
     *
     * @return ResultSet
     * @throws ImmutableException
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
            $this->findSpacedKeywords($cleanedQuery, $query->getInstanceId(), $resultSet);
            $resultSet->addProfilePoint('Keywords with space');
        }

        if (count($rawWords) > 0) {
            $this->findSimpleKeywords($rawWords, $query->getInstanceId(), $resultSet);
            $resultSet->addProfilePoint('Simple keywords');

            $this->findFulltext($rawWords, $query->getInstanceId(), $resultSet);
            $resultSet->addProfilePoint('Fulltext search');
        }

        $resultSet->freeze();

        $foundExternalIds = $resultSet->getFoundExternalIds();
        foreach ($this->storage->getTocByExternalIds($foundExternalIds) as $tocEntryWithExternalId) {
            $resultSet->attachToc($tocEntryWithExternalId);
        }

        $resultSet->removeDataWithoutToc();

        return $resultSet;
    }
}
