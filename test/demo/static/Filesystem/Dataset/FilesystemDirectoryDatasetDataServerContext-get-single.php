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
$select_handle = $dataset->getSelectHandle([
    'basename',
    'size',
]);

/* Get Single By Primary Container */

$fetch_manager = $dataset->getFetchManager();
$data_server_context = $fetch_manager->getSingleByPrimaryContainer($select_handle, $filename . '/file-1.txt');

foreach ($data_server_context as $model) {
    echo "Basename: ";
    var_dump($model->basename);
    echo "Filename: ";
    var_dump($model->filename);
    echo "Extension: ";
    var_dump($model->extension);
    echo "Size: ";
    var_dump($model->size);
}
