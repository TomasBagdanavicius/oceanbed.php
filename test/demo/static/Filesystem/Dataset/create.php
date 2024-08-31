<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Dataset\FilesystemDatabase;

$filesystem_database = new FilesystemDatabase();
$address_name = (Demo\TEST_PATH . '/bin/filesystem/tmp');
$dataset = $filesystem_database->initDataset($address_name);
$store_handle = $dataset->getStoreHandle();
$create_manager = $store_handle->getCreateManager();

/* Single */

$result = $create_manager->singleFromArray([
    'basename' => 'tmpfile-0.txt',
    'type' => 'file',
], commit: false); // Commit control

/* Single Duplicate */

/* $result = $create_manager->singleFromArray([
    'basename' => 'to-truncate.txt',
    'type' => 'file',
], commit: false); // Commit control */

/* Many */

/* $result = $create_manager->manyFromArray([
    [
        'basename' => 'tmpfile-X.txt',
        'type' => 'file',
    ], [
        'basename' => 'tmpfile-Y.txt',
        'type' => 'file',
    ], [
        'basename' => 'tmpfile-Z.txt',
        'type' => 'file',
    ],
], commit: false); // Commit control */

pr($result);
