<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\FileType\File;
use LWP\Filesystem\Path\PathEnvironmentRouter;
use LWP\Filesystem\Exceptions\DuplicateFileException;
use LWP\Filesystem\Exceptions\FileCreateError;

$path = realpath(Demo\TEST_PATH . '/bin/filesystem/tmp');

do {
    $pathname = ($path . '/tmpfile-' . mt_rand() . '.txt');
} while (file_exists($pathname));

$path_handler = PathEnvironmentRouter::getStaticInstance();
$file_path = $path_handler::getFilePathInstance($pathname);

try {
    $file = File::create($file_path);
    $result = true;
} catch (DuplicateFileException|FileCreateError) {
    $result = false;
}

if ($result && !unlink($pathname)) {
    throw new \RuntimeException(sprintf(
        "Could not delete temporary file %s",
        $pathname
    ));
}

Demo\assert_true($result, "This is my error message");
