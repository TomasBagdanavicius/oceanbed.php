<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(__DIR__ . '/shared/data.php');
include(__DIR__ . '/shared/custom-descriptor.php');

use LWP\Components\Datasets\ArrayCollectionDataset\ArrayCollectionDatabase;
use LWP\Components\Datasets\ArrayCollectionDataset\ArrayCollectionDataset;
use LWP\Common\Conditions\Condition;

$database = new ArrayCollectionDatabase();
$dataset = $database->initDataset('my_dataset', [
    $data,
    $definition_data_array,
]);
$store_handle = $dataset->getStoreHandle();
$create_manager = $store_handle->getCreateManager();

/* Single */

/* $result = $create_manager->singleFromArray([
    'id' => '15',
    'name' => 'Tom',
    'age' => '22',
    'occupation' => 'Student',
    'height' => '1.90',
], commit: true); */

/* Single Duplicate */

/* $result = $create_manager->singleFromArray([
    'id' => '1',
    'name' => 'Mary',
    'age' => '27',
    'occupation' => 'Stylist',
    'height' => '1.69',
], commit: true); */

/* Many */

$result = $create_manager->manyFromArray([
    [
        'id' => 15,
        'name' => 'Tom',
        'age' => '22',
        'occupation' => 'Student',
        'height' => '1.90',
    ], [
        'id' => 1,
        'name' => 'Mary',
        'age' => '27',
        'occupation' => 'Stylist',
        'height' => '1.69',
    ], [
        'id' => 11,
        'name' => 'Steve',
        'age' => 46,
        'occupation' => 'Engineer',
        'height' => '1.79',
    ]
], commit: true);

pr($result);

echo "Find created entry: ";
// Required "commit" enabled
var_dump(iterator_count($dataset->byConditionObject(new Condition('name', "Tom"))));
