<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Iterators\MyRecursiveDirectoryIterator;
use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Path\PosixPath;

$file_path = PosixPath::getFilePathInstance(realpath(Demo\TEST_PATH . '/bin/filesystem/read'));
$directory = new Directory($file_path);

$my_recursive_directory_iterator = new MyRecursiveDirectoryIterator($directory);
$my_recursive_directory_iterator = new RecursiveIteratorIterator(
    $my_recursive_directory_iterator,
    // Override LEAVES_ONLY
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($my_recursive_directory_iterator as $pathname => $file) {

    echo $pathname, ' ', $file::class, PHP_EOL;
}
