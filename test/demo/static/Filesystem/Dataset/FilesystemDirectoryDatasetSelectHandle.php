<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Path\PosixPath;
use LWP\Filesystem\Dataset\FilesystemDirectoryDataset;
use LWP\Components\Datasets\Attributes\SelectAllAttribute;

$filename = realpath(Demo\TEST_PATH . '/bin/filesystem/read');
$file_path = PosixPath::getFilePathInstance($filename);
$directory = new Directory($file_path);
$filesystem_directory_dataset = new FilesystemDirectoryDataset($directory);

$select_handle = $filesystem_directory_dataset->getSelectHandle([
    'basename',
    'size',
]);

echo "Class name: ";
var_dump($select_handle::class);

echo "All identifiers: ";
pr($select_handle->getAllIdentifiers());

echo "Relationship identifiers: ";
pr($select_handle->getExtrinsicContainerList());
