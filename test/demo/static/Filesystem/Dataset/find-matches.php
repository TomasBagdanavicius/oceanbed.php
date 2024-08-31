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
use LWP\Components\Model\ModelCollection;

$filename = realpath(Demo\TEST_PATH . '/bin/filesystem/read');
$file_path = PosixPath::getFilePathInstance($filename);
$directory = new Directory($file_path);
$dataset = new FilesystemDirectoryDataset($directory);

$select_handle = $dataset->getSelectHandle();
$fetch_manager = $dataset->getFetchManager();

$model1 = $dataset->getModel();
$dataset->setupModelPopulateCallbacks($model1);
$model2 = clone $model1;

$model1->basename = 'en-pangram.txt';
$model1->type = 'file';

$model2->basename = 'abc.txt';
$model2->type = 'file';

$model_collection = new ModelCollection();
$model_collection->add($model1);
$model_collection->add($model2);

$result = $fetch_manager->findMatches($select_handle, $model_collection);

echo "Found count: ";
if ($result) {
    var_dump($result->count());
} else {
    echo '0';
}
