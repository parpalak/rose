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
use S2\Search\Storage\File\SingleFileArrayStorage;

/**
 * Class IndexerTest
 */
class IntegrationTest extends Unit
{
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

	/**
	 * @dataProvider indexableProvider
	 *
	 * @param Indexable[] $indexables
	 */
	public function testFeatures(array $indexables)
	{
		$filename = $this->getTempFilename() . '.tmp';

		$stemmer = new PorterStemmerRussian();
		$storage = new SingleFileArrayStorage($filename);
		$indexer = new Indexer($storage, $stemmer);

		// We're working on an empty storage
		// $storage->load();

		foreach ($indexables as $indexable) {
			$indexer->add($indexable);
		}

		$storage->cleanup();
		$storage->save();

		// Reinit storage
		$storage        = new SingleFileArrayStorage($filename);
		$finder         = new Finder($storage, $stemmer);
		$snippetBuilder = new SnippetBuilder($storage, $stemmer);

		$snippetCallbackProvider = function (array $ids) use ($indexables) {
			$result = [];
			foreach ($indexables as $indexable) {
				if (in_array($indexable->getId(), $ids)) {
					$result[$indexable->getId()] = $indexable->getContent();
				}
			}

			return $result;
		};

		$storage->load();

		$result1 = $finder->find('snippets');
		$this->assertEquals([], $result1->getWeightByExternalId(), 'Do not index description');

		$result2  = $finder->find('content');
		$snippets = $snippetBuilder->getSnippets($result2, $snippetCallbackProvider);
		$this->assertEquals(['id_2' => 31, 'id_1' => 1], $result2->getWeightByExternalId());
		$this->assertEquals('I have to make up a <i>content</i>.', $snippets['id_1']->getValue());
		$this->assertEquals('This is the second page to be indexed. Let\'s compose something new.', $snippets['id_2']->getValue());

		$result3  = $finder->find('сущность Plus');
		$snippets = $snippetBuilder->getSnippets($result3, $snippetCallbackProvider);
		$this->assertEquals('Тут есть тонкость - нужно проверить, как происходит экранировка в <i>сущностях</i> вроде +.', $snippets['id_3']->getValue());
	}

	public function indexableProvider()
	{
		return [
			[
				[
					(new Indexable('id_1', 'Test page', 'This is the first page to be indexed. I have to make up a content.'))
						->setKeywords('singlekeyword, multiple keywords')
						->setDescription('The description can be used for snippets')
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
				],
			],
		];
	}

	public function testProfiling()
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
		$filenames = array_slice($filenames, 0, 14);

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

		foreach (array_merge($indexProfilePoints, $loadingProfilePoints, $result->getProfilePoints()) as $point) {
			codecept_debug(Helper::formatProfilePoint($point));
		}
	}
}
