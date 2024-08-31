<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\FileType\File;
use LWP\Filesystem\Path\PathEnvironmentRouter;

$pathname = realpath(Demo\TEST_PATH . '/bin/filesystem/static/files/hello-world.txt');

if ($pathname === false) {
    throw new \RuntimeException(sprintf(
        "File %s does not exist",
        $pathname
    ));
}

$path_handler = PathEnvironmentRouter::getStaticInstance();
$file_path = $path_handler::getFilePathInstance($pathname);

$file = new File($file_path);

Demo\assert_true(
    !$file->isEmpty(),
    "A non-empty file is incorrectly pronounced as empty"
);
