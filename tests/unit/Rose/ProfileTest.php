<?php
/**
 * @copyright 2016-2017 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Test;

use Codeception\Test\Unit;
use S2\Rose\Entity\Indexable;
use S2\Rose\Entity\Query;
use S2\Rose\Finder;
use S2\Rose\Helper\Helper;
use S2\Rose\Indexer;
use S2\Rose\SnippetBuilder;
use S2\Rose\Stemmer\PorterStemmerRussian;
use S2\Rose\Storage\Database\PdoStorage;
use S2\Rose\Storage\File\SingleFileArrayStorage;

/**
 * Class ProfileTest
 *
 * @group profile
 */
class ProfileTest extends Unit
{
    const TEST_FILE_NUM = 17;

    /**
     * @return string
     */
    private function getTempFilename()
    {
        return __DIR__ . '/../../tmp/index.php';
    }

    protected function _before()
    {
        @unlink($this->getTempFilename());
    }

    /*
        public function testSnippet()
        {
            $start = microtime(true);

            $filenames = glob(__DIR__ . '/../../Resource/data/' . '*.txt');
            $filenames = array_slice($filenames, 0, self::TEST_FILE_NUM);

            $stemmer        = new PorterStemmerRussian();
            $snippetBuilder = new SnippetBuilder($stemmer);

            $indexProfilePoints[] = Helper::getProfilePoint('Preparing data', -$start + ($start = microtime(true)));

            $contentArray = [];
            foreach ($filenames as $filename) {
                $contentArray[] = file_get_contents($filename);
            }
            $indexProfilePoints[] = Helper::getProfilePoint('reading', -$start + ($start = microtime(true)));

            $contentArray = $snippetBuilder->cleanupContent($contentArray);
            $indexProfilePoints[] = Helper::getProfilePoint('cleanup', -$start + ($start = microtime(true)));

            $start2 = $start;

            foreach ($contentArray as $content) {
                $snippet = $snippetBuilder->buildSnippet(['test' => [83, 90], 'test2' => [49, 55, 142]], $content);

                $indexProfilePoints[] = Helper::getProfilePoint('pre-building', -$start + ($start = microtime(true)));

                $snippet = $snippet->toString();
    //			codecept_debug($snippet);

                $indexProfilePoints[] = Helper::getProfilePoint('post-building', -$start + ($start = microtime(true)));
            }

            $indexProfilePoints[] = Helper::getProfilePoint('building', -$start2 + (microtime(true)));

    //		codecept_debug($matches);

            foreach (array_merge($indexProfilePoints) as $point) {
                codecept_debug(Helper::formatProfilePoint($point));
            }
        }
    */
    public function testSnippets()
    {
        $start = microtime(true);

        return;
        $filenames = glob(__DIR__ . '/../../Resource/data/' . '*.txt');
        $filenames = array_slice($filenames, 0, self::TEST_FILE_NUM);

        $indexProfilePoints[] = Helper::getProfilePoint('Preparing data', -$start + ($start = microtime(true)));

        $stems = ['захот', 'разговарива', 'мно', 'никогда'];
        $regex = '#(?<=[^a-zа-я]|^)(' . implode('|', $stems) . ')[a-zа-я]*#Ssui';

        $contentArray = [];
        foreach ($filenames as $filename) {
            $contentArray[] = file_get_contents($filename);
        }
        $indexProfilePoints[] = Helper::getProfilePoint('reading', -$start + ($start = microtime(true)));

        for ($i = 5; $i--;) {
            foreach ($contentArray as $content) {
                preg_match_all($regex, $content, $matches, PREG_OFFSET_CAPTURE);
            }
        }
        $indexProfilePoints[] = Helper::getProfilePoint('matching 1', -$start + ($start = microtime(true)));

        $content = implode("\r", $contentArray);
        unset($contentArray);

        $indexProfilePoints[] = Helper::getProfilePoint('concat', -$start + ($start = microtime(true)));

        for ($i = 5; $i--;) {
            preg_match_all($regex, $content, $matches, PREG_OFFSET_CAPTURE);
        }

        $indexProfilePoints[] = Helper::getProfilePoint('matching 2', -$start + ($start = microtime(true)));

//		codecept_debug($matches);

        foreach (array_merge($indexProfilePoints) as $point) {
            codecept_debug(Helper::formatProfilePoint($point));
        }

    }

    public function testFileProfiling()
    {
        $start = microtime(true);

        $stemmer = new PorterStemmerRussian();
        $storage = new SingleFileArrayStorage($this->getTempFilename());
        $indexer = new Indexer($storage, $stemmer);

        $indexProfilePoints[] = Helper::getProfilePoint('Indexer initialization', -$start + ($start = microtime(true)));

        $indexProfilePoints = array_merge(
            $indexProfilePoints,
            $storage->load(true)
        );

        $indexProfilePoints[] = Helper::getProfilePoint('Storage loading', -$start + ($start = microtime(true)));

        $filenames = glob(__DIR__ . '/../../Resource/data/' . '*.txt');
        $filenames = array_slice($filenames, 0, self::TEST_FILE_NUM);

        $indexProfilePoints[] = Helper::getProfilePoint('Preparing data', -$start + ($start = microtime(true)));

        foreach ($filenames as $filename) {
            $content   = file_get_contents($filename);
            $indexable = new Indexable(
                basename($filename),
                substr($content, 0, strpos($content, "\n")),
                $content
            );

//			$indexProfilePoints[] = Helper::getProfilePoint('Reading item', -$start + ($start = microtime(true)));

            $indexer->index($indexable);

//			$indexProfilePoints[] = Helper::getProfilePoint('Indexing item', -$start + ($start = microtime(true)));
        }

        $indexProfilePoints[] = Helper::getProfilePoint('Indexing', -$start + ($start = microtime(true)));

        $storage->cleanup();

        $indexProfilePoints[] = Helper::getProfilePoint('Storage cleanup', -$start + ($start = microtime(true)));

        $storage->save();

        $indexProfilePoints[] = Helper::getProfilePoint('Storage save', -$start + ($start = microtime(true)));

        $storage = new SingleFileArrayStorage($this->getTempFilename());
        $finder  = new Finder($storage, $stemmer);

        $indexProfilePoints[] = Helper::getProfilePoint('Finder initialization', -$start + ($start = microtime(true)));

        $loadingProfilePoints = $storage->load(true);

        $result = $finder->find(new Query('захотел разговаривать'), true);

        $snippetBuilder = new SnippetBuilder($stemmer);
        $snippetBuilder->attachSnippets($result, function (array $ids) {
            $data = [];
            foreach ($ids as $id) {
                $data[$id] = file_get_contents(__DIR__ . '/../../Resource/data/' . $id);
            }

            return $data;
        });

        foreach (array_merge($indexProfilePoints, $loadingProfilePoints, $result->getProfilePoints()) as $point) {
            codecept_debug(Helper::formatProfilePoint($point));
        }
    }

    public function testDatabaseProfiling()
    {
        $start = microtime(true);

        global $s2_rose_test_db;

        $pdo = new \PDO($s2_rose_test_db['dsn'], $s2_rose_test_db['username'], $s2_rose_test_db['passwd']);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $storage = new PdoStorage($pdo, 'profiling_');

        $indexProfilePoints[] = Helper::getProfilePoint('Db initialization', -$start + ($start = microtime(true)));

        $storage->erase();

        $indexProfilePoints[] = Helper::getProfilePoint('Db cleanup', -$start + ($start = microtime(true)));

        $stemmer = new PorterStemmerRussian();
        $indexer = new Indexer($storage, $stemmer);

        $indexProfilePoints[] = Helper::getProfilePoint('Indexer initialization', -$start + ($start = microtime(true)));

        $filenames = glob(__DIR__ . '/../../Resource/data/' . '*.txt');
        $filenames = array_slice($filenames, 0, self::TEST_FILE_NUM);

        $indexProfilePoints[] = Helper::getProfilePoint('Preparing data', -$start + ($start = microtime(true)));

        foreach ($filenames as $filename) {
            $content   = file_get_contents($filename);
            $indexable = new Indexable(
                basename($filename),
                substr($content, 0, strpos($content, "\n")),
                $content
            );

//			$indexProfilePoints[] = Helper::getProfilePoint('Reading item', -$start + ($start = microtime(true)));

            $indexer->index($indexable);

//			$indexProfilePoints[] = Helper::getProfilePoint('Indexing item', -$start + ($start = microtime(true)));
        }

        $indexProfilePoints[] = Helper::getProfilePoint('Indexing', -$start + ($start = microtime(true)));

        $storage = new PdoStorage($pdo, 'profiling_');
        $finder  = new Finder($storage, $stemmer);

        $indexProfilePoints[] = Helper::getProfilePoint('Finder initialization', -$start + ($start = microtime(true)));

        $result = $finder->find(new Query('захотел разговаривать'), true);

        $snippetBuilder = new SnippetBuilder($stemmer);
        $snippetBuilder->attachSnippets($result, function (array $ids) {
            $data = [];
            foreach ($ids as $id) {
                $data[$id] = file_get_contents(__DIR__ . '/../../Resource/data/' . $id);
            }

            return $data;
        });

        foreach (array_merge($indexProfilePoints, $result->getProfilePoints()) as $point) {
            codecept_debug(Helper::formatProfilePoint($point));
        }
    }
}
