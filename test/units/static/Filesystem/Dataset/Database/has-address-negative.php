<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Dataset\FilesystemDatabase;

$filesystem_database = new FilesystemDatabase();

// Generates a pathname that represents an unexisting file
$c = 1;
do {
    $pathname = (__DIR__ . DIRECTORY_SEPARATOR . 'dir' . $c);
    $c++;
} while (file_exists($pathname));

Demo\assert_true(
    $filesystem_database->hasAddress($pathname) === false,
    "Unexpected result"
);
