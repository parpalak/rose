# Rose
This is a simple search engine for content sites with simplified English and Russian morphology support.
It indexes your content and provides a full-text search.

## Requirements

1. PHP 5.6 or later. ![Build Status](https://github.com/parpalak/rose/actions/workflows/test.yml/badge.svg?branch=master)
2. A relational database (MySQL is supported for now) in case of significant content size.

## Installation

```
composer require s2/rose
```

If you do not use composer, download the archive, unpack it somewhere and ensure including php-files from src/ directory based on a PSR-0/4 scheme. Though you really should use composer.

## Usage
### Preparing Storage
The index can be stored in a database or in a file. Storage is an abstraction layer that hides implementation details.
In most cases you gonna need database storage `PdoStorage`.

Both indexing and searching require the storage.

```php
$pdo = new \PDO('mysql:host=127.0.0.1;dbname=s2_rose_test;charset=utf8', 'username', 'passwd');
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

use S2\Rose\Storage\Database\PdoStorage;

$storage = new PdoStorage($pdo, 'table_prefix_');
```

When you want to rebuild the index, you call `PdoStorage::erase()` method:
```php
$storage->erase();
```

It drops index tables (if exist) and creates new ones from scratch. This method will be enough to upgrade to a new version of Rose that breaks down the backward compatibility of the index.

### Morphology

For natural language processing, Rose uses stemmers.
Stemmer cuts off the changing part of words and Rose deals with stems.
It has no built-in dictionaries but contains heuristic stemmers developed by Porter.
You can integrate any other algorithm by implementing the StemmerInterface.

```php
use S2\Rose\Stemmer\PorterStemmerEnglish;
use S2\Rose\Stemmer\PorterStemmerRussian;

// For optimization primary language goes first (in this case Russian)
$stemmer = new PorterStemmerRussian(new PorterStemmerEnglish());
```

### Indexing

`Indexer` builds the search index. It depends on a stemmer and a storage.

```php
use S2\Rose\Indexer;

$indexer = new Indexer($storage, $stemmer);
```

Indexer accepts your data in a special format. The data must be wrapped in the `Indexable` class:

```php
use S2\Rose\Entity\Indexable;

// required params
$indexable = new Indexable(
	'id_1',            // External ID - an identifier in your system 
	'Test page title', // Title 
	'This is the first page to be indexed. I have to make up a content.',
	1                  // Instance ID - an optional ID of your subsystem 
);

// optional params
$indexable
	->setKeywords('singlekeyword, multiple keywords')       // The same as Meta Keywords
	->setDescription('Description can be used in snippets') // The same as Meta Description
	->setDate(new \DateTime('2016-08-24 00:00:00'))
	->setUrl('url1')
;

$indexer->index($indexable);

$indexable = new Indexable(
	'id_2',
	'Test page title 2',
	'This is the second page to be indexed. Let\'s compose something new.'
);
$indexable->setKeywords('content, page');

$indexer->index($indexable);
```

The constructor of `Indexable` requires 4 arguments:
- external ID - an arbitrary string ID that is sufficient for your code to identify the page;
- page title;
- page content;
- instance ID - an optional int ID of the page source (e.g. for multi-site services).

You may also provide some optional parameters: keywords, description, date and URL. Keywords affect the relevance. The description can be used for building a snippet (see below). It's a good idea to use the content of "keyword" and "description" meta-tags for this purpose (if you have any, of course). The URL can be an arbitrary string.

The `Indexer::index()` method is used both for adding and updating the index. If the content is not changed, this method skips the job. Otherwise, the content is being removed and indexed again.

When you remove a page from the site, just call

```php
$indexer->removeById($externalId, $instanceId);
```

### Searching

Full-text search results can be obtained via `Finder` class.
`$resultSet->getItems()` returns all the information about content items and their relevance.

```php
use S2\Rose\Finder;
use S2\Rose\Entity\Query;

$finder    = new Finder($storage, $stemmer);
$resultSet = $finder->find(new Query('content'));

foreach ($resultSet->getItems() as $item) {
	                         // first iteration:          second iteration:
	$item->getId();          // 'id_2'                    'id_1'
	$item->getInstanceId();  // null                      1
	$item->getTitle();       // 'Test page title 2'       'Test page title'
	$item->getUrl();         // ''                        'url1'
	$item->getDescription(); // ''                        'Description can be used in snippets'
	$item->getDate();        // null                      new \DateTime('2016-08-24 00:00:00')
	$item->getRelevance();   // 31.0                      1.0
	$item->getSnippet();     // ''                        'Description can be used in snippets'
}
```

Modify the `Query` object to use a pagination:
```php
$query = new Query('content');
$query
	->setLimit(10)  // 10 results per page
	->setOffset(20) // third page
;
$resultSet = $finder->find($query);

$resultSet->getTotalCount(); // Returns total amount of found items (for pagination links)
```

Adjust the relevance for favorite and popular pages:
```php
use S2\Rose\Entity\ExternalId;

$resultSet = $finder->find(new Query('content'));
$externalId1 = $resultSet->getFoundExternalIds()->toArray()[0];
var_dump($externalId1->getId(), $externalId1->getInstanceId()); // id_1 1
$resultSet->setRelevanceRatio($externalId1, 3.14);
$resultSet->setRelevanceRatio(new ExternalId('id_2', null), 2);

foreach ($resultSet->getItems() as $item) {
	                         // first iteration:          second iteration:
	$item->getId();          // 'id_2'                    'id_1'
	$item->getRelevance();   // 62.0                      3.14
}
```

Provide instance id to limit the scope of the search with a subsystem:
```php
$resultSet = $finder->find((new Query('content'))->setInstanceId(1));
$resultSet->setRelevanceRatio('id_1', 3.14);

foreach ($resultSet->getItems() as $item) {
	                         // first iteration:
	$item->getId();          // 'id_1'
	$item->getInstanceId();  // 1
}
```

### Highlighting and Snippets

It's a common practice to highlight the found words in the search results. You can obtain the highlighted title:

```php
$resultSet = $finder->find(new Query('title'));
$resultSet->getItems()[0]->getHighlightedTitle($stemmer); // 'Test page <i>title</i>'
```

This method requires the stemmer since it takes into account the morphology and highlights all the word forms. By default, words are highlighted with italics. You can change the highlight template by calling `$finder->setHighlightTemplate('<b>%s</b>')`.

Snippets are small text fragments containing found words displaying in the search result. `SnippetBuilder` processes the source and selects best matching sentences. It should be done just before `$resultSet->getItems()`:

```php
use S2\Rose\Entity\ExternalContent;
use S2\Rose\SnippetBuilder;

$snippetBuilder = new SnippetBuilder($stemmer);
$snippetBuilder->setSnippetLineSeparator(' &middot; '); // Set snippet line separator. Default is '... '.
$snippetBuilder->attachSnippets($resultSet, static function (array $externalIds) {
    /** @var \S2\Rose\Entity\ExternalId[] $externalIds */

	$result = new ExternalContent();
	foreach ($externalIds as $externalId) {
		if ($externalId->getId() === 'id_1') {
			$result->attach($externalId, 'This page is to be indexed. I have to make up a content.');
		}
		else {
			$result->attach($externalId, 'This is the second page to be indexed. Let\'s compose something new.');
		}
	}
	return $result;
});

$resultSet->getItems()[0]->getSnippet(); // 'I have to make up a <i>content</i>.'
```

Words in snippets are highlighted the same way as in titles.

Building snippets is quite a heavy operation. Use it with pagination to reduce the snippet generation time.
