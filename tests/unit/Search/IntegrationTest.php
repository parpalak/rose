<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

namespace S2\Search\Test;

use Codeception\Test\Unit;
use S2\Search\Entity\Indexable;
use S2\Search\Entity\Query;
use S2\Search\Finder;
use S2\Search\Indexer;
use S2\Search\SnippetBuilder;
use S2\Search\Stemmer\PorterStemmerRussian;
use S2\Search\Storage\Database\PdoStorage;
use S2\Search\Storage\File\SingleFileArrayStorage;
use S2\Search\Storage\StorageReadInterface;
use S2\Search\Storage\StorageWriteInterface;

/**
 * Class IndexerTest
 */
class IntegrationTest extends Unit
{
	const TEST_FILE_NUM = 14;

	/**
	 * @return string
	 */
	private function getTempFilename()
	{
		return __DIR__ . '/../../tmp/index2.php';
	}

	protected function _before()
	{
		@unlink($this->getTempFilename());
	}

	/**
	 * @dataProvider indexableProvider
	 *
	 * @param Indexable[]           $indexables
	 * @param StorageReadInterface  $readStorage
	 * @param StorageWriteInterface $writeStorage
	 */
	public function testFeatures(
		array $indexables,
		StorageReadInterface $readStorage,
		StorageWriteInterface $writeStorage
	) {
		$stemmer = new PorterStemmerRussian();
		$indexer = new Indexer($writeStorage, $stemmer);

		// We're working on an empty storage
		if ($writeStorage instanceof PdoStorage) {
			$writeStorage->erase();
		}

		foreach ($indexables as $indexable) {
			$indexer->add($indexable);
		}

		if ($writeStorage instanceof SingleFileArrayStorage) {
			$writeStorage->cleanup();
			$writeStorage->save();
		}

		// Reinit storage
		if ($readStorage instanceof SingleFileArrayStorage) {
			$readStorage->load();
		}
		$finder         = new Finder($readStorage, $stemmer);
		$snippetBuilder = new SnippetBuilder($stemmer);

		$snippetCallbackProvider = function (array $ids) use ($indexables) {
			$result = [];
			foreach ($indexables as $indexable) {
				if (in_array($indexable->getId(), $ids)) {
					$result[$indexable->getId()] = $indexable->getContent();
				}
			}

			return $result;
		};

		// Query 1
		$resultSet1 = $finder->find(new Query('snippets'));
		$this->assertEquals([], $resultSet1->getWeightByExternalId(), 'Do not index description');

		// Query 2
		$resultSet2 = $finder->find(new Query('content'));

		$this->assertEquals(['id_2' => 31, 'id_1' => 1], $resultSet2->getWeightByExternalId());

		$items = $resultSet2->getItems();
		$this->assertEquals('Description can be used in snippets', $items['id_1']->getSnippet());

		$snippetBuilder->attachSnippets($resultSet2, $snippetCallbackProvider);

		$items = $resultSet2->getItems();

		$this->assertEquals('Test page title',                     $items['id_1']->getTitle());
		$this->assertEquals('url1',                                $items['id_1']->getUrl());
		$this->assertEquals('Description can be used in snippets', $items['id_1']->getDescription());
		$this->assertEquals(new \DateTime('2016-08-24 00:00:00'),  $items['id_1']->getDate());
		$this->assertEquals(1.0,                                   $items['id_1']->getRelevancy());
		$this->assertEquals('I have to make up a <i>content</i>.', $items['id_1']->getSnippet());

		$this->assertEquals(31, $items['id_2']->getRelevancy());
		$this->assertEquals('This is the second page to be indexed. Let\'s compose something new.', $items['id_2']->getSnippet());

		// Query 3
		$resultSet3 = $finder->find(new Query('сущность Plus'));
		$snippetBuilder->attachSnippets($resultSet3, $snippetCallbackProvider);
		$this->assertEquals('Тут есть тонкость - нужно проверить, как происходит экранировка в <i>сущностях</i> вроде +.', $resultSet3->getItems()['id_3']->getSnippet());
	}

	public function indexableProvider()
	{
		$indexables = [
			(new Indexable('id_1', 'Test page title', 'This is the first page to be indexed. I have to make up a content.'))
				->setKeywords('singlekeyword, multiple keywords')
				->setDescription('Description can be used in snippets')
				->setDate(new \DateTime('2016-08-24 00:00:00'))
				->setUrl('url1')
			,
			(new Indexable('id_2', 'To be continued...', 'This is the second page to be indexed. Let\'s compose something new.'))
				->setKeywords('content, ')
				->setDescription('')
				->setDate(new \DateTime('2016-08-20 00:00:00'))
				->setUrl('any string')
			,
			(new Indexable('id_3', 'Русский текст', '<p>Для проверки работы нужно написать побольше слов. Вот еще одно предложение.</p><p>Тут есть тонкость - нужно проверить, как происходит экранировка в сущностях вроде &plus;. Для этого нужно включить в текст само сочетание букв "plus".</p>'))
				->setKeywords('ключевые слова')
				->setDescription('')
				->setDate(new \DateTime('2016-08-22 00:00:00'))
				->setUrl('/якобы.урл')
			,
		];

		global $s2_search_test_db;
		$pdo = new \PDO($s2_search_test_db['dsn'], $s2_search_test_db['username'], $s2_search_test_db['passwd']);
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

		$filename = $this->getTempFilename();

		return [
			'files' => [$indexables, new SingleFileArrayStorage($filename), new SingleFileArrayStorage($filename)],
			'db'    => [$indexables, new PdoStorage($pdo, 'test_'), new PdoStorage($pdo, 'test_')],
		];
	}
}
