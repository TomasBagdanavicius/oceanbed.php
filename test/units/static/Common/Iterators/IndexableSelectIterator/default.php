<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Iterators\IndexableSelectIterator;
use LWP\Filesystem\FileType\DirectoryReader;
use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Path\PosixPath;

$file_path = PosixPath::getFilePathInstance(realpath(Demo\TEST_PATH . '/bin/filesystem/read'));
$directory = new Directory($file_path);
$property_list = [
    'pathname',
    'basename',
    'size'
];
$iterator = new IndexableSelectIterator(
    new DirectoryReader($directory, DirectoryReader::RECURSE),
    $property_list
);

foreach ($iterator as $pathname => $data) {
    $keys = array_keys($data);
    if ($keys !== $property_list) {
        throw new \RuntimeException("Property list does not match for element $pathname");
    }
}

Demo\assert_true(true, "Unexpected result");
