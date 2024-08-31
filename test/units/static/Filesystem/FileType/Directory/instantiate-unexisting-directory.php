<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/units/shared/utilities.php');

use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Path\PosixPath;
use LWP\Filesystem\Exceptions\FileNotFoundException;
use LWP\Filesystem\Path\PathEnvironmentRouter;

$pathname = getLocationInFilesystemBin('empty');
$path_handler = PathEnvironmentRouter::getStaticInstance();
$file_path = $path_handler::getFilePathInstance($pathname . DIRECTORY_SEPARATOR . 'unexisting-directory');
$expected_thrown = false;

try {
    $directory = new Directory($file_path);
} catch (FileNotFoundException) {
    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Unexisting directory pathname was not declined"
);
