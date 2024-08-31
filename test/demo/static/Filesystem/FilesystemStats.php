<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\FilesystemStats;
use LWP\Filesystem\Enums\FileActionEnum;
use LWP\Filesystem\Path\PosixPath;
use LWP\Common\FileLogger;
use LWP\Common\Enums\StatusEnum;

$file_logger = new FileLogger(Demo\TEST_PATH . '/log/test.log');

$filesystem_stats = new FilesystemStats();

$filesystem_stats->registerSuccess(FileActionEnum::CREATE, '/foo/bar/file.txt');
$filesystem_stats->registerSuccess(FileActionEnum::DELETE, '/foo/bar/file.txt');
$file_path = PosixPath::getFilePathInstance('/foo/baz/file-2.txt');
$filesystem_stats->registerFailure(FileActionEnum::CREATE, $file_path);
$filesystem_stats->registerFound(FileActionEnum::CREATE, '/foo/bar/file-3.txt');
$filesystem_stats->registerNotFound(FileActionEnum::COPY, '/foo/bar/file-4.txt');

var_dump($filesystem_stats->getSummaryText());
print_r($filesystem_stats->getResults());
print_r($filesystem_stats->getLastResults());
