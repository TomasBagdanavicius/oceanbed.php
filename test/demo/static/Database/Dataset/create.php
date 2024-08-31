<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

$address_name = 'static';
$dataset = $database->initDataset($address_name);
$store_handle = $dataset->getStoreHandle();
$create_manager = $store_handle->getCreateManager();

/* Single */

$result = $create_manager->singleFromArray([
    'date_created' => date('Y-m-d H:i:s'),
    'title' => 'Barbaz',
    'name' => 'barbaz',
], commit: false); // Commit control

/* Duplicate */

/* $result = $create_manager->singleFromArray([
    'date_created' => date('Y-m-d H:i:s'),
    'title' => 'Lorem',
    'name' => 'lorem',
], commit: false); // Commit control */

/* Many */

/* $result = $create_manager->manyFromArray([
    [
        'date_created' => date('Y-m-d H:i:s'),
        'title' => 'Barbaz',
        'name' => 'barbaz',
    ], [
        'date_created' => date('Y-m-d H:i:s'),
        'title' => 'Lorem',
        'name' => 'lorem',
    ],
], commit: false); // Commit control */

pr($result);
