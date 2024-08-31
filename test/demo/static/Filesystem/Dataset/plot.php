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
$filesystem_directory_dataset = new FilesystemDirectoryDataset($directory);

$model = $filesystem_directory_dataset->getModel();
$filesystem_directory_dataset->getRelationalModelFromFullIntrinsicDefinitions(
    $model,
    field_value_extension: false,
    // This must be turned off when batch solution is used
    dataset_unique_constraint: false
);

$model->basename = 'file-1.txt';
$model->type = 'file';

$filesystem_directory_dataset->batchValidateUniqueContainers($model);

pr($model->getValuesWithMessages());
