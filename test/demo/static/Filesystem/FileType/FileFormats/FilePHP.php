<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\FileType\File;
use LWP\Filesystem\FileType\FileFormats\FilePhp;
use LWP\Filesystem\Path\PosixPath;

$pathname = (Demo\TEST_PATH . '/bin/php-file.php');
$file_path = PosixPath::getFilePathInstance($pathname);
$file = new FilePhp($file_path);

pr($file->getTokenList());
