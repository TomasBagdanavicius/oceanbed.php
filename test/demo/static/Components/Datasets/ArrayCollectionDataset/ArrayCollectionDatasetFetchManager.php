<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(__DIR__ . '/shared/data.php');

use LWP\Common\Conditions\Condition;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Components\Datasets\ArrayCollectionDataset\ArrayCollectionDatabase;

$database = new ArrayCollectionDatabase();
$dataset = $database->initDataset('dataset', [
    $data,
    $definition_data_array,
    'primary_container_name' => 'id',
]);

$select_handle = $dataset->getSelectHandle();
$fetch_manager = $dataset->getFetchManager();

echo "Class name: ";
var_dump($fetch_manager::class);


/* Single by Unique Container */

/* $data_server_context = $fetch_manager->getSingleByUniqueContainer($select_handle, 'id', '1');
$model = $data_server_context->getModel();
var_dump($model->getValues()); */


/* Get All */

/* $result = $fetch_manager->getAll($select_handle);

foreach( $result as $model ) {
    echo $model->id, ' ', $model->name, ' ', $model->occupation, PHP_EOL;
} */


/* By Condition */

/* $condition = new Condition('name', "John");
$result = $fetch_manager->getByCondition($condition);

foreach( $result as $model ) {
    echo $model->id, ' ', $model->name, ' ', $model->occupation, PHP_EOL;
} */


/* Filter By Values */

/* $result = $fetch_manager->filterByValues($select_handle, 'name', ['John', 'Jane']);

foreach( $result as $model ) {
    echo $model->id, ' ', $model->name, ' ', $model->occupation, PHP_EOL;
} */


/* Filter By Pairs */

/* $pairs = [
    'name' => 'John',
];
$result = $fetch_manager->filterByPairs($select_handle, $pairs, NamedOperatorsEnum::AND);

foreach( $result as $model ) {
    echo $model->id, ' ', $model->name, ' ', $model->occupation, PHP_EOL;
} */
