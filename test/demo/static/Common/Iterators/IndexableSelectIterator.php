<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
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
$iterator = new IndexableSelectIterator(
    new DirectoryReader($directory, DirectoryReader::RECURSE),
    [
        'pathname',
        'basename',
        'size',
    ]
);

foreach ($iterator as $data) {
    pr($data);
}
