<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Dataset\FilesystemDatabase;
use LWP\Filesystem\Dataset\FilesystemDirectoryDataset;

$filesystem_database = new FilesystemDatabase();

$address_name = (Demo\TEST_PATH . '/bin/filesystem/read');
$dataset = $filesystem_database->initDataset($address_name);

Demo\assert_true($dataset instanceof FilesystemDirectoryDataset, "Unexpected result");
