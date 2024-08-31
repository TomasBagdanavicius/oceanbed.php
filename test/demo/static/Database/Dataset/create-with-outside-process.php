<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

use LWP\Database\TableDatasetStoreManagementProcess;

$address_name = 'static';
$dataset = $database->initDataset($address_name);
$process = new TableDatasetStoreManagementProcess($dataset->database);
$store_handle = $dataset->getStoreHandle();
$create_manager = $store_handle->getCreateManager(extra_params: [
    'process' => $process
]);

/* Single */

$result = $create_manager->singleFromArray([
    'date_created' => date('Y-m-d H:i:s'),
    'title' => 'Barbaz',
    'name' => 'barbaz',
], commit: false); // Commit control

pr($result);
