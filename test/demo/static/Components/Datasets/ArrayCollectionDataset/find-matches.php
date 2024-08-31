<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(__DIR__ . '/shared/data.php');

use LWP\Components\Datasets\ArrayCollectionDataset\ArrayCollectionDatabase;
use LWP\Components\Datasets\ArrayCollectionDataset\ArrayCollectionDataset;
use LWP\Components\Datasets\Attributes\SelectAllAttribute;
use LWP\Components\Model\ModelCollection;

$database = new ArrayCollectionDatabase();
$dataset = $database->initDataset('dataset', [
    $data,
    $definition_data_array,
    'primary_container_name' => 'id',
]);

$select_handle = $dataset->getSelectHandle();
$fetch_manager = $dataset->getFetchManager();

$model1 = $dataset->getModel();
$dataset->setupModelPopulateCallbacks($model1);
$model2 = clone $model1;

$model1->id = 3;
$model1->age = 30;
$model1->name = 'Jack';
$model1->occupation = 'Driver';
$model1->height = '1.78';

$model2->id = 20;
$model2->age = 44;
$model2->name = 'Mary';
$model2->occupation = 'Banking Consultant';
$model2->height = '1.66';

$model_collection = new ModelCollection();
$model_collection->add($model1);
$model_collection->add($model2);

$result = $fetch_manager->findMatches($select_handle, $model_collection);

echo "Found count: ";
var_dump($result->count());
