<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\FileType\File;
use LWP\Filesystem\Path\PathEnvironmentRouter;
use LWP\Filesystem\Exceptions\FileCopyError;

$path = realpath(Demo\TEST_PATH . '/bin/filesystem/tmp');
$path_handler = PathEnvironmentRouter::getStaticInstance();

$filename = ($path . '/to-copy.txt');
$file_path = $path_handler::getFilePathInstance($filename);

$destination_pathname = ($path . '/copy-destination/copied.txt');
$destination_file_path = $path_handler::getFilePathInstance($destination_pathname);

$file = new File($file_path);

try {
    $new_file = $file->copy($destination_file_path);
    $new_file_pathname = $new_file->pathname;
    $result = true;
} catch (FileCopyError) {
    $result = false;
}

if ($result && !unlink($destination_pathname)) {
    throw new \RuntimeException(sprintf(
        "Could not delete copied file %s",
        $destination_pathname
    ));
}

Demo\assert_true($result, "Could not copy file");
