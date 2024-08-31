<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Path\PosixPath;
use LWP\Filesystem\FileType\File;
use LWP\Filesystem\Exceptions\FileDeleteError;
use LWP\Filesystem\Path\PathEnvironmentRouter;

$path = realpath(Demo\TEST_PATH . '/bin/filesystem/tmp');

do {
    $pathname = ($path . '/tmpfile-' . mt_rand() . '.txt');
} while (file_exists($pathname));

$handle = fopen($pathname, 'w');

if ($handle) {

    if (file_exists($pathname)) {

        $path_handler = PathEnvironmentRouter::getStaticInstance();
        $file_path = $path_handler::getFilePathInstance($pathname);
        $file = new File($file_path);

        try {
            $result = $file->delete();
        } catch (FileDeleteError) {
            $result = false;
        }

    } else {

        throw new \RuntimeException(sprintf(
            "Could not create temporary file %s",
            $pathname
        ));
    }

    if (!fclose($handle)) {

        throw new \RuntimeException(sprintf(
            "Could not close file handle for file %s",
            $pathname
        ));
    }

} else {

    throw new \RuntimeException(sprintf(
        "Could not open file handle to %s",
        $pathname
    ));
}

Demo\assert_true($result, "Failed to delete file");
