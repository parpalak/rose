# Rose

This is a search engine designed for content sites with simplified yet functional English and Russian morphology support.
It indexes your content and provides a full-text search.

## Requirements

1. PHP 7.4 or later. ![Build Status](https://github.com/parpalak/rose/actions/workflows/test.yml/badge.svg?branch=master)
2. A relational database in case of significant content size. MySQL 5.7+ and MariaDB 10.2+ are supported.

## Installation

```
composer require s2/rose
```

## Usage
### Preparing Storage
The index can be stored in a database or in a file. A storage serves as an abstraction layer that conceals implementation details.
In most cases you need database storage `PdoStorage`.

The storage is required for both indexing and searching.

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

It drops index tables (if they exist) and creates new ones from scratch.
This method is sufficient for upgrading to a new version of Rose that may not be backward compatible with the existing index.

### Morphology

For natural language processing, Rose uses stemmers.
The stemmer truncates the inflected part of words, and Rose processes the resulting stems.
Rose does not have built-in dictionaries, but it includes heuristic stemmers developed by Porter.
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

// Main parameters
$indexable = new Indexable(
	'id_1',            // External ID - an identifier in your system 
	'Test page title', // Title 
	'This is the first page to be indexed. I have to make up a content.',
	1                  // Instance ID - an optional ID of your subsystem 
);

// Other optional parameters
$indexable
	->setKeywords('singlekeyword, multiple keywords')       // The same as Meta Keywords
	->setDescription('Description can be used in snippets') // The same as Meta Description
	->setDate(new \DateTime('2016-08-24 00:00:00'))
	->setUrl('url1')
	->setRelevanceRatio(3.14)                               // Multiplier for important pages
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
- external ID - an arbitrary string that is sufficient for your code to identify the page;
- page title;
- page content;
- instance ID - an optional integer ID of the page source (e.g., for multi-site services), as explained below.

Optional parameters that you can provide include: keywords, description, date, relevance ratio, and URL.
Keywords are indexed and searched with higher relevance.
The description can be used for building a snippet (see below).
The URL can be an arbitrary string.

It is suggested to use the content of "keyword" and "description" meta-tags, if available, for this purpose.

The `Indexer::index()` method is used for both adding and updating the index.
If the content is unchanged, this method skips the operation. Otherwise, the content is being removed and indexed again.

When you remove a page from the site, simply call

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
	                         // first iteration:              second iteration:
	$item->getId();          // 'id_2'                        'id_1'
	$item->getInstanceId();  // null                          1
	$item->getTitle();       // 'Test page title 2'           'Test page title'
	$item->getUrl();         // ''                            'url1'
	$item->getDescription(); // ''                            'Description can be used in snippets'
	$item->getDate();        // null                          new \DateTime('2016-08-24 00:00:00')
	$item->getRelevance();   // 4.1610856664112195            0.26907154598642522
	$item->getSnippet();     // 'This is the second page...'  'I have to make up a <i>content</i>.'
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

Provide instance id to limit the scope of the search with a subsystem:
```php
$resultSet = $finder->find((new Query('content'))->setInstanceId(1));

foreach ($resultSet->getItems() as $item) {
	                         // first iteration only:
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

Snippets are small text fragments containing found words that are displayed at a search results page. Rose processes the indexed content and selects best matching sentences.

```php
use S2\Rose\Entity\ExternalContent;
use S2\Rose\Snippet\SnippetBuilder;

$finder->setSnippetLineSeparator(' &middot; '); // Set snippet line separator. Default is '... '.

$resultSet->getItems()[0]->getSnippet();
// 'I have to make up a <i>content</i>. &middot; I have changed the <i>content</i>.'
```

Words in the snippets are highlighted the same way as in titles.

If building snippets takes a lot of time, try to use pagination to reduce the number of snippets processed. 

### Instances

Instances can be helpful to restrict the scope of search.

For example, you can try to index blog posts with `instance_id = 1` and comments with `instance_id = 2`.
Then you can run queries with different restrictions:
- `(new Query('content'))->setInstanceId(1)` searches through blog posts,
- `(new Query('content'))->setInstanceId(2)` searches through comments,
- `(new Query('content'))` searches everywhere.

If you omit instance_id or provide `instance_id === null`, a value `0` will be used internally. This content can match only queries without instance_id restriction.

### Content format and extraction

Rose is designed for the websites and web applications.
It supports HTML format of the content by default.
However, it is possible to extend the code to support other formats (e.g. plain text, markdown).
This can be done by creating a custom extractor:

```php
use S2\Rose\Extractor\ExtractorInterface;
use S2\Rose\Indexer;

class CustomExtractor implements ExtractorInterface
{
    // ...
    // Please refer to the source code
    // to figure out how to create an extractor. 
} 

$indexer = new Indexer($storage, $stemmer, new CustomExtractor(), new Logger());
```

### Recommendations

PdoStorage has the capability to identify similar items within the entire set of indexed items.

Consider a scenario where you have a blog and its posts are indexed using Rose.
This particular feature allows you to choose a set of other posts for each individual post, enabling visitors to explore related content.

The data structure within the full-text index is well-suited for the task of selecting similar posts.
To put it simply, regular search entails selecting relevant posts based on words from a search query,
whereas post recommendations involve selecting other posts based on the words present in a given post.

You can retrieve recommendations by invoking the following method:

```php
$similarItems = $readStorage->getSimilar(new ExternalId('id_2'));
// The result contains the following data:
// $similarItems[0] = [
//     'tocWithMetadata' => new TocEntryWithMetadata(...),
//     'external_id'     => 'id_1',
//     'instance_id'     => '1',
//     'title'           => 'Test page title',
//     'snippet'         => 'This is the first page to be indexed.',
//     'snippet2'        => 'I have to make up a content.',
// ],
```
