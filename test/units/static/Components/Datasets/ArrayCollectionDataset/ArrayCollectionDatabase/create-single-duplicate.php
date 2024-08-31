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
use LWP\Components\Datasets\Enums\DatasetActionStatusEnum;

$database = new ArrayCollectionDatabase();
$dataset = $database->initDataset('my_dataset', [
    $data,
    $definition_data_array,
]);
$store_handle = $dataset->getStoreHandle();
$create_manager = $store_handle->getCreateManager();

$result = $create_manager->singleFromArray([
    'id' => '1',
    'name' => 'Mary',
    'age' => '27',
    'occupation' => 'Stylist',
    'height' => '1.69',
], commit: true);

Demo\assert_true(
    $result['data']['my_dataset'][0]['status'] === DatasetActionStatusEnum::FOUND->value,
    "Unexpected result"
);
