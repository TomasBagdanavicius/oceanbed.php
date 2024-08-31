<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(__DIR__ . '/shared/data-2.php');

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

$action_params = $fetch_manager::getModelForActionType('read');
#$action_params->page_number = 2;
#$action_params->limit = 2;
$action_params->search_query = 'te';
#$action_params->search_query_mark = 1;
#$action_params->sort = 'name, id';
#$action_params->order = 'desc';

pr($action_params->getValues());

$data_server_context = $fetch_manager->list($select_handle, $action_params);

echo PHP_EOL;
foreach ($data_server_context as $model) {
    echo '#', $model->id, ' ', $model->name, ' | ', $model->occupation, PHP_EOL;
}

include(Demo\TEST_PATH . '/demo/shared/generic-pager.php');
