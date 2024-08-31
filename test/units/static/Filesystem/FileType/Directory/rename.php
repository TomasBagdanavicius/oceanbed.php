<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/units/shared/utilities.php');

use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Path\PathEnvironmentRouter;

$pathname = createTempDirInFilesystemBin();
$path_handler = PathEnvironmentRouter::getStaticInstance();
$file_path = $path_handler::getFilePathInstance($pathname);
$new_filename = pathinfo(generateTempFilePathname(getFilesystemBinTmpDirPathname(), extension: false), PATHINFO_FILENAME);
$directory = new Directory($file_path);

try {
    $new_directory = $directory->rename($new_filename);
    $new_directory_pathname = $new_directory->pathname;
    $result = true;
} catch (FileRenameError) {
    $result = false;
}

deleteDir($new_directory_pathname);

Demo\assert_true($result, "This is my error message");
