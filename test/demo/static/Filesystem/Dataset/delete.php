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
$delete_manager = $store_handle->getDeleteManager();

$result = $delete_manager->deleteOne('basename', 'to-delete.txt', commit: false);

pr($result);
