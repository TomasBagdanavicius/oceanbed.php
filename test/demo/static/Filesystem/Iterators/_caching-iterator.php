<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Path\PosixPath;
use LWP\Filesystem\Iterators\MyRecursiveDirectoryIterator;
use LWP\Common\Iterators\AccumulativeIndexableColumnIterator;
use LWP\Common\Iterators\AccumulativeIndexableIterator;
use LWP\Common\String\Clause\SortByComponent;
use LWP\Common\Iterators\AccumulativeIterator;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\sortByColumns;

$file_path = PosixPath::getFilePathInstance(realpath(Demo\TEST_PATH . '/bin/filesystem/read'));
$directory = new Directory($file_path);

$iterator = new MyRecursiveDirectoryIterator($directory);
#$iterator = new RecursiveCachingIterator($iterator, \CachingIterator::FULL_CACHE);
$iterator = new RecursiveIteratorIterator($iterator);
#$i1 = $iterator = new AccumulativeIndexableColumnIterator($iterator);
#$iterator = new AccumulativeIndexableIterator($iterator);
$iterator = new AccumulativeIterator($iterator);

foreach ($iterator as $item) {
    #var_dump($item->getPathname());
}

$storage = $iterator->getStorage();



#$columns = $i1->getStorage();
#$array = $iterator->getStorage();

#$sort_by_component = SortByComponent::fromString('extension DESC, size DESC');
#sortByColumns($array, $columns, $sort_by_component);

#pr($columns);
#pre($array);

#$storage_iterator = new \ArrayIterator($iterator->getCache());



$sort_by_str = 'basename ASC, extension ASC';
$sort_handler = SortByComponent::getSortHandlerForIndexableObject($storage[0], $sort_by_str);




$iterator = new \ArrayIterator($storage);
$iterator->uasort($sort_handler);

foreach ($iterator as $key => $value) {

    var_dump($value->pathname);
}
