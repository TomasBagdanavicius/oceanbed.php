<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\FileType\File;
use LWP\Filesystem\Path\PathEnvironmentRouter;

$pathname = realpath(Demo\TEST_PATH . '/bin/filesystem/static/files/multiline.txt');
$path = PathEnvironmentRouter::getStaticInstance();
$file_path = $path::getFilePathInstance($pathname);

$file = new File($file_path);
$total_lines = 7;

Demo\assert_true(
    $file->countLines() === $total_lines,
    "Incorrect line number count"
);
