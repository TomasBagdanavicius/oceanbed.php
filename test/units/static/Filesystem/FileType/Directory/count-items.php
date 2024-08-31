<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/units/shared/utilities.php');

use LWP\Filesystem\Path\PathEnvironmentRouter;
use LWP\Filesystem\FileType\Directory;

$pathname = getLocationInFilesystemBin('read');
$path_handler = PathEnvironmentRouter::getStaticInstance();
$file_path = $path_handler::getFilePathInstance($pathname);

$directory = new Directory($file_path);

Demo\assert_true(
    $directory->count() === 15,
    "Directory returned wrong number of items inside"
);
