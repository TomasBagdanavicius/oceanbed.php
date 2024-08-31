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

$pathname = realpath(Demo\TEST_PATH . '/bin/filesystem/read');
$file_path = PosixPath::getFilePathInstance($pathname);
$directory = new Directory($file_path);
$dataset = new FilesystemDirectoryDataset($directory);

$model = clone $dataset->getModel();
$dataset->setupModelPopulateCallbacks($model);
$model->basename = 'file-1.txt';
$standard_unique_case = $dataset->buildStandardUniqueCase($model);

Demo\assert_true(
    $standard_unique_case->__toString() === "pathname = $pathname/file-1.txt OR name = $pathname/file-1.txt OR basename = file-1.txt",
    "Unexpected result"
);
