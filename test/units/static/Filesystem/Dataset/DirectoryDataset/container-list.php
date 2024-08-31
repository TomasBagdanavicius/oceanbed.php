<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Dataset\FilesystemDirectoryDataset;
use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Path\PosixPath;

$pathname = realpath(Demo\TEST_PATH . '/bin/filesystem');
$file_path = PosixPath::getFilePathInstance($pathname);
$directory = new Directory($file_path);
$dataset = new FilesystemDirectoryDataset($directory);

Demo\assert_true(
    $dataset->getContainerList() === [
        'pathname',
        'name',
        'filename',
        'extension',
        'basename',
        'type',
        'size',
        'date_last_modified',
    ],
    "Unexpected result"
);
