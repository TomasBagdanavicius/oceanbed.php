<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(Demo\TEST_PATH . '/demo/static/Components/Datasets/ArrayCollectionDataset/shared/data.php');

use LWP\Common\Conditions\Condition;
use LWP\Components\Datasets\ArrayCollectionDataset\ArrayCollectionDatabase;
use LWP\Components\Datasets\ArrayCollectionDataset\ArrayCollectionDataset;

$database = new ArrayCollectionDatabase();
$dataset = $database->initDataset('my_dataset', [
    $data,
    $definition_data_array,
]);
$store_handle = $dataset->getStoreHandle();
$create_manager = $store_handle->getCreateManager();

$result = $create_manager->singleFromArray([
    'id' => '15',
    'name' => 'Tom',
    'age' => '22',
    'occupation' => 'Student',
    'height' => '1.90',
// Commit is required for this test
], commit: true);

# Debug
#pre($dataset->column_array_collection->toArray());

Demo\assert_true(
    // "commit" that is required for this to work is enabled
    iterator_count($dataset->byConditionObject(new Condition('name', "Tom"))) === 1,
    "Unexpected result"
);
