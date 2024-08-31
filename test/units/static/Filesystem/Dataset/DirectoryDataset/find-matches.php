<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/units/shared/utilities.php');

use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Dataset\FilesystemDirectoryDataset;
use LWP\Filesystem\Path\PathEnvironmentRouter;
use LWP\Components\Model\ModelCollection;

$pathname = getLocationInFilesystemBin('read');
$path_handler = PathEnvironmentRouter::getStaticInstance();
$file_path = $path_handler::getFilePathInstance($pathname);

$directory = new Directory($file_path);
$dataset = new FilesystemDirectoryDataset($directory);
$select_handle = $dataset->getSelectHandle();
$fetch_manager = $dataset->getFetchManager();
$model1 = $dataset->getModel();
$dataset->setupModelPopulateCallbacks($model1);
$model2 = clone $model1;
$model3 = clone $model1;

$model1->basename = 'abc.txt';
$model2->basename = 'qsefthuko.txt'; // Unexisting
$model3->basename = 'file-1.txt';

$model_collection = new ModelCollection();
$model_collection->add($model1);
$model_collection->add($model2);
$model_collection->add($model3);

$result = $fetch_manager->findMatches($select_handle, $model_collection);
$count = $result->count();

Demo\assert_true($count === 2, "Expected 2 matches, got $count");
