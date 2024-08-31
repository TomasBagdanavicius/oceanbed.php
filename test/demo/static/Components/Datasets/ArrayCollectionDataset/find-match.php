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

$database = new ArrayCollectionDatabase();
$dataset = $database->initDataset('dataset', [
    $data,
    $definition_data_array,
    'primary_container_name' => 'id',
]);
$select_handle = $dataset->getSelectHandle();
$fetch_manager = $dataset->getFetchManager();

$model = $dataset->getModel();
$dataset->setupModelPopulateCallbacks($model);

$model->id = 3;
$model->age = 30;
$model->name = 'Jack';
$model->occupation = 'Driver';
$model->height = '1.78';

$match = $fetch_manager->findMatch($select_handle, $model);

if ($match) {
    echo "Match found: ";
    $entries = iterator_to_array($match);
    $entry = $entries[array_key_first($entries)];
    pr($entry);
} else {
    prl("No match was found");
}
