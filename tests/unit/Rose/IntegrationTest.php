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
use S2\Rose\Indexer;
use S2\Rose\SnippetBuilder;
use S2\Rose\Stemmer\PorterStemmerRussian;
use S2\Rose\Storage\Database\PdoStorage;
use S2\Rose\Storage\File\SingleFileArrayStorage;
use S2\Rose\Storage\StorageReadInterface;
use S2\Rose\Storage\StorageWriteInterface;

/**
 * Class IntegrationTest
 *
 * @group int
 */
class IntegrationTest extends Unit
{
	const TEST_FILE_NUM = 17;

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
			$indexer->index($indexable);
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
		$this->assertEquals([], $resultSet1->getSortedRelevanceByExternalId(), 'Do not index description');

		// Query 2
		$resultSet2 = $finder->find(new Query('content'));

		$this->assertEquals(['id_2' => 31, 'id_1' => 1], $resultSet2->getSortedRelevanceByExternalId());

		$items = $resultSet2->getItems();
		$this->assertEquals('Description can be used in snippets', $items['id_1']->getSnippet());

		$snippetBuilder->attachSnippets($resultSet2, $snippetCallbackProvider);

		$items = $resultSet2->getItems();

		$this->assertEquals('Test page title',                     $items['id_1']->getTitle());
		$this->assertEquals('url1',                                $items['id_1']->getUrl());
		$this->assertEquals('Description can be used in snippets', $items['id_1']->getDescription());
		$this->assertEquals(new \DateTime('2016-08-24 00:00:00'),  $items['id_1']->getDate());
		$this->assertEquals(1.0,                                   $items['id_1']->getRelevance());
		$this->assertEquals('I have changed the <i>content</i>.',  $items['id_1']->getSnippet());

		$this->assertEquals(31, $items['id_2']->getRelevance());
		$this->assertEquals('This is the second page to be indexed. Let\'s compose something new.', $items['id_2']->getSnippet());

		$resultSet2 = $finder->find(new Query('content'));
		$resultSet2->setRelevanceRatio('id_1', 3.14);

		$this->assertEquals(['id_2' => 31, 'id_1' => 3.14], $resultSet2->getSortedRelevanceByExternalId());

		$resultSet2 = $finder->find(new Query('content'));
		$resultSet2->setRelevanceRatio('id_1', 100);
		$resultItems = $resultSet2->getItems();
		$this->assertCount(2, $resultItems);
		$this->assertEquals('id_1', array_keys($resultItems)[0], 'Sorting by relevance is not working');
		$this->assertEquals(100, $resultItems['id_1']->getRelevance());

		$resultSet2 = $finder->find(new Query('title'));
		$this->assertEquals('Test page <i>title</i>', $resultSet2->getItems()['id_1']->getHighlightedTitle($stemmer));

		// Query 3
		$resultSet3 = $finder->find(new Query('сущность Plus'));
		$snippetBuilder->attachSnippets($resultSet3, $snippetCallbackProvider);
		$this->assertEquals(
			'Тут есть тонкость - нужно проверить, как происходит экранировка в <i>сущностях</i> вроде +. Для этого нужно включить в текст само сочетание букв "<i>plus</i>".',
			$resultSet3->getItems()['id_3']->getSnippet()
		);

		// Query 4
		$resultSet4 = $finder->find(new Query('эпл'));
		$this->assertCount(1, $resultSet4->getItems());

		$snippetBuilder->attachSnippets($resultSet4, $snippetCallbackProvider);
		$this->assertEquals(
			'Например, красно-черный, <i>эпл</i>-вотчем, и другие интересные комбинации.',
			$resultSet4->getItems()['id_3']->getSnippet()
		);

		$finder->setHighlightTemplate('<b>%s</b>');
		$resultSet4   = $finder->find(new Query('красный заголовку'));
		$resultItems4 = $resultSet4->getItems();
		$this->assertCount(1, $resultItems4);

		$snippetBuilder->attachSnippets($resultSet4, $snippetCallbackProvider);
		$this->assertEquals(
			'Например, <b>красно</b>-черный, эпл-вотчем, и другие интересные комбинации.',
			$resultItems4['id_3']->getSnippet()
		);
		$this->assertEquals(
			'Русский текст. <b>Красным</b> <b>заголовком</b>',
			$resultItems4['id_3']->getHighlightedTitle($stemmer)
		);

		// Query 5
		$resultSet5 = $finder->find(new Query('русский'));
		$this->assertCount(1, $resultSet5->getItems());
		$this->assertEquals(20, $resultSet5->getItems()['id_3']->getRelevance());

		$resultSet5 = $finder->find(new Query('русскому'));
		$this->assertCount(1, $resultSet5->getItems());
		$this->assertEquals(20, $resultSet5->getItems()['id_3']->getRelevance());

	}

	/**
	 * @dataProvider indexableProvider
	 *
	 * @param Indexable[]           $indexables
	 * @param StorageReadInterface  $readStorage
	 * @param StorageWriteInterface $writeStorage
	 */
	public function testParallelIndexingAndSearching(
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

		$indexer->index($indexables[0]);
		if ($writeStorage instanceof SingleFileArrayStorage) {
			$writeStorage->cleanup();
			$writeStorage->save();
		}

		// Reinit storage
		if ($readStorage instanceof SingleFileArrayStorage) {
			$readStorage->load();
		}

		$finder = new Finder($readStorage, $stemmer);
		$finder->find(new Query('page'));  // a word in $indexables[0]

		if ($writeStorage instanceof SingleFileArrayStorage) {
			$writeStorage->load();
		}
		$indexer->index($indexables[1]);
		if ($writeStorage instanceof SingleFileArrayStorage) {
			$writeStorage->cleanup();
			$writeStorage->save();
		}

		$finder->find(new Query('page')); // a word in $indexables[1]
	}

	public function testIgnoreFrequentWords()
	{
		global $s2_rose_test_db;
		$pdo = new \PDO($s2_rose_test_db['dsn'], $s2_rose_test_db['username'], $s2_rose_test_db['passwd']);
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

		$storage = new PdoStorage($pdo, 'test_');
		$stemmer = new PorterStemmerRussian();
		$indexer = new Indexer($storage, $stemmer);
		$finder  = new Finder($storage, $stemmer);

		$storage->erase();

		for ($i = 10; $i-- ;) {
			$indexable = new Indexable('id_' . $i, 'title ' . $i, 'text ' . $i);
			$indexer->index($indexable);
		}
		$indexable = new Indexable('id_test', 'text 123', 'body');
		$indexer->index($indexable);

		$resultSet = $finder->find(new Query('text'));
		$this->assertCount(11, $resultSet->getItems());

		$storage->erase();
		for ($i = 50; $i-- ;) {
			$indexable = new Indexable('id_' . $i, 'title ' . $i, 'text ' . $i);
			$indexer->index($indexable);
		}
		$indexable = new Indexable('id_test', 'text 123', 'body');
		$indexer->index($indexable);

		$resultSet = $finder->find(new Query('text'));
		$this->assertCount(0, $resultSet->getItems());
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
			(new Indexable('id_3', 'Русский текст. Красным заголовком', '<p>Для проверки работы нужно написать побольше слов. Вот еще одно предложение.</p><p>Тут есть тонкость - нужно проверить, как происходит экранировка в сущностях вроде &plus;. Для этого нужно включить в текст само сочетание букв "plus".</p><p>Еще одна особенность - наличие слов с дефисом. Например, красно-черный, эпл-вотчем, и другие интересные комбинации.</p>'))
				->setKeywords('ключевые слова')
				->setDescription('')
				->setDate(new \DateTime('2016-08-22 00:00:00'))
				->setUrl('/якобы.урл')
			,
			(new Indexable('id_1', 'Test page title', 'This is the first page to be indexed. I have changed the content.'))
				->setKeywords('singlekeyword, multiple keywords')
				->setDescription('Description can be used in snippets')
				->setDate(new \DateTime('2016-08-24 00:00:00'))
				->setUrl('url1')
			,
		];

		global $s2_rose_test_db;
		$pdo = new \PDO($s2_rose_test_db['dsn'], $s2_rose_test_db['username'], $s2_rose_test_db['passwd']);
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

		$filename = $this->getTempFilename();

		return [
			'files' => [$indexables, new SingleFileArrayStorage($filename), new SingleFileArrayStorage($filename)],
			'db'    => [$indexables, new PdoStorage($pdo, 'test_'), new PdoStorage($pdo, 'test_')],
		];
	}
}
