<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/units/shared/utilities.php');

use LWP\Filesystem\Path\PathEnvironmentRouter;
use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Dataset\FilesystemDirectoryDataset;

$pathname = getLocationInFilesystemBin('read');
$path_handler = PathEnvironmentRouter::getStaticInstance();
$file_path = $path_handler::getFilePathInstance($pathname);

$basename_to_match = 'abc.txt';
$pathname_to_match = ($pathname . DIRECTORY_SEPARATOR . $basename_to_match);

if (!file_exists($pathname_to_match)) {
    throw new \RuntimeException(sprintf(
        "File %s that is required to look for a match was not found",
        $pathname_to_match
    ));
}

$directory = new Directory($file_path);
$dataset = new FilesystemDirectoryDataset($directory);
$select_handle = $dataset->getSelectHandle();
$fetch_manager = $dataset->getFetchManager();
$model = $dataset->getModel();
$dataset->setupModelPopulateCallbacks($model);

$model->basename = $basename_to_match;

$match = $fetch_manager->findMatch($select_handle, $model);

Demo\assert_true(
    $match !== null && $match->count() === 1,
    "Match not found or there is more than one match"
);
