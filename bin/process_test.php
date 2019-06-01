<?php
/**
 * @copyright 2016 Roman Parpalak
 * @license   MIT
 */

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../tests/config.php';

define('TEST_FILE_NUM', 17);

$pdo = new \PDO($s2_rose_test_db['dsn'], $s2_rose_test_db['username'], $s2_rose_test_db['passwd']);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

$storage = new \S2\Rose\Storage\Database\PdoStorage($pdo, 'multiprocess_');
// $storage->erase();

$stemmer = new \S2\Rose\Stemmer\PorterStemmerRussian();
$indexer = new \S2\Rose\Indexer($storage, $stemmer);

$filenames = glob(__DIR__ . '/../tests/Resource/data/' . '*.txt');
$filenames = array_slice($filenames, 0, TEST_FILE_NUM);

foreach ($filenames as $filename) {
    echo 'Indexing ', $filename, "\n";
    $content   = file_get_contents($filename) . ' ' . rand();
    $indexable = new \S2\Rose\Entity\Indexable(
        basename($filename),
        substr($content, 0, strpos($content, "\n")),
        $content
    );

    $indexer->index($indexable);
}
