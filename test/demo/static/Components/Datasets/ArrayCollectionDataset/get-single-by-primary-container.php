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

$database = new ArrayCollectionDatabase();
$dataset = $database->initDataset('dataset', [
    $data,
    $definition_data_array,
    'primary_container_name' => 'id',
]);
$select_handle = $dataset->getSelectHandle([
    'id',
    'name',
    'occupation',
]);
$fetch_manager = $dataset->getFetchManager();
$data_server_context = $fetch_manager->getSingleByPrimaryContainer($select_handle, 3);

foreach ($data_server_context as $model) {
    var_dump($model->id, $model->name, $model->occupation);
}
