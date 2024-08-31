<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(__DIR__ . '/shared/data.php');

use LWP\Components\Datasets\ArrayCollectionDataset\ArrayCollectionDatabase;
use LWP\Components\Datasets\ArrayCollectionDataset\ArrayCollectionDataset;

$database = new ArrayCollectionDatabase();
$dataset = $database->initDataset('dataset', [
    $data,
    $definition_data_array,
    'primary_container_name' => 'id',
]);
$model = $dataset->getModel();
$dataset->getRelationalModelFromFullIntrinsicDefinitions(
    $model,
    field_value_extension: false,
    // This must be turned off when batch solution is used
    dataset_unique_constraint: false
);

$model->id = 3; // "3" is taken; try "30" to avoid error
$model->age = 35;
$model->name = 'Jack';
$model->occupation = 'Driver';
$model->height = '1.78';

$dataset->batchValidateUniqueContainers($model);

pr($model->getValuesWithMessages());
