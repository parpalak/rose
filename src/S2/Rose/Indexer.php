<?php
/**
 * Creates search index
 *
 * @copyright 2010-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\Indexable;
use S2\Rose\Exception\RuntimeException;
use S2\Rose\Exception\UnknownException;
use S2\Rose\Helper\StringHelper;
use S2\Rose\Stemmer\StemmerInterface;
use S2\Rose\Storage\Exception\EmptyIndexException;
use S2\Rose\Storage\StorageEraseInterface;
use S2\Rose\Storage\StorageWriteInterface;
use S2\Rose\Storage\TransactionalStorageInterface;

class Indexer
{
    /**
     * @var StorageWriteInterface
     */
    protected $storage;

    /**
     * @var StemmerInterface
     */
    protected $stemmer;

    /**
     * @var bool
     */
    private $autoErase = false;

    /**
     * @param StorageWriteInterface $storage
     * @param StemmerInterface      $stemmer
     */
    public function __construct(StorageWriteInterface $storage, StemmerInterface $stemmer)
    {
        $this->storage = $storage;
        $this->stemmer = $stemmer;
    }

    /**
     * Cleaning up an HTML string.
     *
     * @param string $content
     * @param string $allowedSymbols
     *
     * @return string
     */
    public static function strFromHtml($content, $allowedSymbols = '')
    {
        // Prevents word concatenation like this: "something.</p><p>Something else"
        $content = str_replace('<', ' <', $content);
        $content = strip_tags($content);

        $content = mb_strtolower($content);
        $content = str_replace(['&nbsp;', "\xc2\xa0"], ' ', $content);
        $content = preg_replace('#&[^;]{1,20};#', '', $content);

        // We allow letters, digits and some punctuation: ".,-"
        $content = preg_replace('#[^\\-.,0-9\\p{L}\\^_' . $allowedSymbols . ']+#u', ' ', $content);

        // These punctuation characters are meant to be inside words and numbers.
        // We'll remove trailing characters when splitting the words.
        $content .= ' ';

        return $content;
    }

    /**
     * @param string $contents
     *
     * @return string[]
     */
    protected static function arrayFromStr($contents)
    {
        $words = preg_split('#[\\-.,]*?[ ]+#S', $contents);
        $words = StringHelper::removeLongWords($words);

        return $words;
    }

    /**
     * @param string     $word
     * @param ExternalId $externalId
     * @param int        $type
     */
    protected function addKeywordToIndex($word, ExternalId $externalId, $type)
    {
        if ($word === '') {
            return;
        }

        $word = str_replace('ё', 'е', $word);

        if (strpos($word, ' ') !== false) {
            $this->storage->addToMultipleKeywordIndex($word, $externalId, $type);
        } else {
            $this->storage->addToSingleKeywordIndex($word, $externalId, $type);
        }
    }

    /**
     * @param ExternalId $externalId
     * @param string     $title
     * @param string     $content
     * @param string     $keywords
     */
    protected function addToIndex(ExternalId $externalId, $title, $content, $keywords)
    {
        // Processing title
        foreach (self::arrayFromStr($title) as $titleWord) {
            $this->addKeywordToIndex($this->stemmer->stemWord(trim($titleWord)), $externalId, Finder::TYPE_TITLE);
        }

        // Processing keywords
        foreach (explode(',', $keywords) as $item) {
            $this->addKeywordToIndex(trim($item), $externalId, Finder::TYPE_KEYWORD);
        }

        // Fulltext index
        // Remove russian ё from the fulltext index
        $words = self::arrayFromStr(str_replace('ё', 'е',
            $content . ' ' . str_replace(', ', ' ', $keywords)
        ));

        $subWords = [];

        foreach ($words as $i => &$word) {
            if ($this->storage->isExcluded($word)) {
                unset($words[$i]);
                continue;
            }

            // If the word contains the hyphen, add a variant without it
            if (false !== strpbrk($word, '-.,')) {
                foreach (preg_split('#[\-.,]#', $word) as $k => $subWord) {
                    if ($subWord) {
                        $subWords[(string)($i + 0.001 * $k)] = $this->stemmer->stemWord($subWord);
                    }
                }
            }

            $word = $this->stemmer->stemWord($word);
        }
        unset($word);

        $this->storage->addToFulltext($words, $externalId);
        $this->storage->addToFulltext($subWords, $externalId);
    }

    /**
     * @param string   $id
     * @param int|null $instanceId
     */
    public function removeById($id, $instanceId)
    {
        $externalId = new ExternalId($id, $instanceId);
        $this->storage->removeFromIndex($externalId);
        $this->storage->removeFromToc($externalId);
    }

    /**
     * @param Indexable $indexable
     *
     * @throws RuntimeException
     * @throws UnknownException
     */
    public function index(Indexable $indexable)
    {
        try {
            $this->doIndex($indexable);
        } catch (EmptyIndexException $e) {
            if (!$this->autoErase || !$this->storage instanceof StorageEraseInterface) {
                throw $e;
            }

            $this->storage->erase();
            $this->doIndex($indexable);
        }
    }

    /**
     * @param bool $autoErase
     */
    public function setAutoErase($autoErase)
    {
        $this->autoErase = $autoErase;
    }

    /**
     * @param Indexable $indexable
     *
     * @throws RuntimeException
     * @throws UnknownException
     */
    protected function doIndex(Indexable $indexable)
    {
        if ($this->storage instanceof TransactionalStorageInterface) {
            $this->storage->startTransaction();
        }

        try {
            $externalId  = $indexable->getExternalId();
            $oldTocEntry = $this->storage->getTocByExternalId($externalId);

            $this->storage->addEntryToToc($indexable->toTocEntry(), $externalId);

            if (!$oldTocEntry || $oldTocEntry->getHash() !== $indexable->calcHash()) {
                $this->storage->removeFromIndex($externalId);
                $this->addToIndex(
                    $externalId,
                    self::strFromHtml($indexable->getTitle()),
                    self::strFromHtml($indexable->getContent()),
                    $indexable->getKeywords()
                );
            }

            if ($this->storage instanceof TransactionalStorageInterface) {
                $this->storage->commitTransaction();
            }
        } catch (\Exception $e) {
            if ($this->storage instanceof TransactionalStorageInterface) {
                $this->storage->rollbackTransaction();
            }
            if (!($e instanceof RuntimeException)) {
                throw new UnknownException('Unknown exception occurred while indexing.', 0, $e);
            }
            throw $e;
        }
    }
}
