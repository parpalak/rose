<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Search\Test;

use Codeception\Test\Unit;
use S2\Search\Entity\Indexable;
use S2\Search\Finder;
use S2\Search\Helper\Helper;
use S2\Search\Indexer;
use S2\Search\SnippetBuilder;
use S2\Search\Stemmer\PorterStemmerRussian;
use S2\Search\Storage\Database\PdoStorage;
use S2\Search\Storage\File\SingleFileArrayStorage;

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

			$indexer->add($indexable);

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

		$result = $finder->find('захотел разговаривать', true);

		$snippetBuilder = new SnippetBuilder($storage, $stemmer);
		$snippets       = $snippetBuilder->getSnippets($result, function (array $ids) {
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

		global $s2_search_test_db;

		$pdo = new \PDO($s2_search_test_db['dsn'], $s2_search_test_db['username'], $s2_search_test_db['passwd']);
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

			$indexer->add($indexable);

//			$indexProfilePoints[] = Helper::getProfilePoint('Indexing item', -$start + ($start = microtime(true)));
		}

		$indexProfilePoints[] = Helper::getProfilePoint('Indexing', -$start + ($start = microtime(true)));

		$storage = new PdoStorage($pdo, 'profiling_');
		$finder  = new Finder($storage, $stemmer);

		$indexProfilePoints[] = Helper::getProfilePoint('Finder initialization', -$start + ($start = microtime(true)));

		$result = $finder->find('захотел разговаривать', true);

		$snippetBuilder = new SnippetBuilder($storage, $stemmer);
		$snippets       = $snippetBuilder->getSnippets($result, function (array $ids) {
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
