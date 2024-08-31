<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Iterators\MyFilesystemIterator;
use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Path\PosixPath;

$file_path = PosixPath::getFilePathInstance(realpath(Demo\TEST_PATH . '/bin/filesystem/read'));
$directory = new Directory($file_path);

$my_filesystem_iterator = new MyFilesystemIterator($directory, MyFilesystemIterator::KEY_AS_RELATIVE_PATHNAME | \FilesystemIterator::SKIP_DOTS);

foreach ($my_filesystem_iterator as $key => $file) {

    echo $file->pathname, ' ', $file::class, PHP_EOL;
}
