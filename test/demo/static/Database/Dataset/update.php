<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

$address_name = 'test';
$dataset = $database->initDataset($address_name);
$store_handle = $dataset->getStoreHandle();
$update_manager = $store_handle->getUpdateManager();

$result = $update_manager->singleFromArray('id', 1, [
    'title' => 'My Title',
], commit: false); // Commit control

pr($result);
