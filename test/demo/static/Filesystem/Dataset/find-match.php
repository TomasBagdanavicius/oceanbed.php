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

$filename = realpath(Demo\TEST_PATH . '/bin/filesystem/read');
$file_path = PosixPath::getFilePathInstance($filename);
$directory = new Directory($file_path);
$dataset = new FilesystemDirectoryDataset($directory);

$select_handle = $dataset->getSelectHandle();
$fetch_manager = $dataset->getFetchManager();

$model = $dataset->getModel();
$dataset->setupModelPopulateCallbacks($model);

$model->basename = 'file-1.txt';

$match = $fetch_manager->findMatch($select_handle, $model);

if ($match) {
    echo "Match found: ";
    pr($match->getFirst()->getIndexableData());
} else {
    prl("No match was found");
}
