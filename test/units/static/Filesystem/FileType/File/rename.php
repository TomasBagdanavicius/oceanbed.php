<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/units/shared/utilities.php');

use LWP\Filesystem\FileType\File;
use LWP\Filesystem\Path\PathEnvironmentRouter;
use LWP\Filesystem\Exceptions\FileRenameError;

[$pathname, $temp_file_handle] = createTempFileInFilesystemBin();
$path_handler = PathEnvironmentRouter::getStaticInstance();
$file_path = $path_handler::getFilePathInstance($pathname);
$new_filename = pathinfo(generateTempFilePathname(getFilesystemBinTmpDirPathname()), PATHINFO_FILENAME);
$file = new File($file_path);

try {
    $new_file = $file->rename($new_filename);
    $new_file_pathname = $new_file->pathname;
    $result = true;
} catch (FileRenameError) {
    $result = false;
}

closeFileHandle($temp_file_handle, $pathname);
deleteFile($new_file_pathname);

Demo\assert_true($result, "Could not rename file");
