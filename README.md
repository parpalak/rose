# Search
This is a simple search engine with Russian morphology for content sites. It indexes your content and provides a full-text search.

## Requirements

1. PHP 5.4 or later.
2. A relational database like MySQL in case of significant content size.

## Installation

```
composer require s2/search
```

## Usage
### Preparing storage
The index can be stored in a database or in a file. Storage is an abstraction layer that hides implementation details.
In most cases you gonna need a database storage `PdoStorage`.

```php
$pdo = new \PDO('mysql:dbname=s2_search_test;host=127.0.0.1', 'username', 'passwd');
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

use S2\Search\Storage\Database\PdoStorage;

$storage = new PdoStorage($pdo, 'table_prefix_');
```

When you want to rebuild the index, you call `PdoStorage::erase()` method:
```php
$storage->erase();
```

### Indexing

`Indexer` builds the search index. It depends on a stemmer and a storage.

```php
use S2\Search\Indexer;
use S2\Search\Stemmer\PorterStemmerRussian;

$stemmer = new PorterStemmerRussian();
$indexer = new Indexer($storage, $stemmer);
```

Indexer accepts your data in a special format. The data must be wrapped in the `Indexable` class:

```php
use S2\Search\Entity\Indexable;

$indexable = new Indexable('id_1', 'Test page title', 'This is the first page to be indexed. I have to make up a content.');
$indexable
	->setKeywords('singlekeyword, multiple keywords')
	->setDescription('The description can be used for snippets')
	->setDate(new \DateTime('2016-08-24 00:00:00'))
	->setUrl('url1')
;

$indexer->add($indexable);

$indexable = new Indexable('id_2', 'To be continued...', 'This is the second page to be indexed. Let\'s compose something new.');
$indexable->setKeywords('content, page');

$indexer->add($indexable);
```

### Searching

Full-text search results can be obtained via `Finder` class.

```php
use S2\Search\Finder;

$finder = new Finder($storage, $stemmer);
$result = $finder->find('content');
$result->getWeightByExternalId(); // ['id_2' => 31, 'id_1' => 1]
```

`$result->getWeightByExternalId();` returns the IDs and relevancy of the content items.

### Snippets

`SnippetBuilder` is a special class that provides text fragments containig found words.

```php
use S2\Search\SnippetBuilder;

$snippetBuilder = new SnippetBuilder($storage, $stemmer);
$snippets = $snippetBuilder->getSnippets($result, $snippetCallbackProvider = function (array $ids) {
	$result = [];
	foreach ($ids as $id) {
		if ($id == 'id_1') {
			$result[$id] = 'This page is to be indexed. I have to make up a content.';
		}
		else {
			$result[$id] = 'This is the second page to be indexed. Let\'s compose something new.';
		}
	}
	return $result;
});

$snippets['id_1']->getValue(); // 'I have to make up a <i>content</i>.'
```

It highlights the found words with italics.
