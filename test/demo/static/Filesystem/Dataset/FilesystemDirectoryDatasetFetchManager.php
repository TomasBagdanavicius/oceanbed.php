<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Conditions\Condition;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Components\Datasets\Attributes\SelectAllAttribute;
use LWP\Filesystem\Dataset\FilesystemDirectoryDataset;
use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Path\PosixPath;

$filename = realpath(Demo\TEST_PATH . '/bin/filesystem/read');
$file_path = PosixPath::getFilePathInstance($filename);
$directory = new Directory($file_path);
$dataset = new FilesystemDirectoryDataset($directory);

$select_handle = $dataset->getSelectHandle();
$fetch_manager = $dataset->getFetchManager();

echo "Class name: ";
var_dump($fetch_manager::class);


/* Single by Unique Container */

/* $data_server_context = $fetch_manager->getSingleByUniqueContainer($select_handle, 'basename', 'abc.txt');
$model = $data_server_context->getModel();
var_dump($model->getValues()); */


/* Get All */

/* $result = $fetch_manager->getAll($select_handle);

foreach( $result as $model ) {
    echo $model->pathname, PHP_EOL;
} */


/* By Condition */

/* $condition = new Condition('extension', 'txt');
$result = $fetch_manager->getByCondition($select_handle, $condition);

foreach( $result as $model ) {
    echo $model->pathname, PHP_EOL;
} */


/* Filter By Values */

/* $result = $fetch_manager->filterByValues($select_handle, 'extension', ['txt', 'md']);

foreach( $result as $model ) {
    echo $model->pathname, PHP_EOL;
} */


/* Filter By Pairs */

/* $pairs = [
    'extension' => 'txt',
    'size' => 3,
];
$result = $fetch_manager->filterByPairs($select_handle, $pairs, NamedOperatorsEnum::AND);

foreach( $result as $model ) {
    echo $model->pathname, PHP_EOL;
} */
