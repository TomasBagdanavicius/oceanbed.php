<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Dataset\FilesystemDatabase;

$filesystem_database = new FilesystemDatabase();
$address_name = realpath(Demo\TEST_PATH . '/bin/filesystem/tmp');
$dataset = $filesystem_database->initDataset($address_name);
$result = $dataset->createEntry([
    'pathname' => ($address_name . '/tmpfile-0.txt'),
    'filename' => 'tmpfile-0',
    'extension' => 'txt',
    'basename' => 'tmpfile-0.txt',
    'type' => 'file',
]);

pre($result);
