<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Dataset\FilesystemDirectoryDataset;
use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Path\PosixPath;
use LWP\Common\Conditions\Condition;

$pathname = realpath(Demo\TEST_PATH . '/bin/filesystem/read');
$file_path = PosixPath::getFilePathInstance($pathname);
$directory = new Directory($file_path);
$dataset = new FilesystemDirectoryDataset($directory);

$condition = new Condition('type', 'directory');
$iterator = $dataset->getContainersByCondition([
    'basename',
    'size',
], $condition);
$array = array_keys(iterator_to_array($iterator));
$expected_array = [
    Demo\TEST_PATH . '/bin/filesystem/read/abc',
    Demo\TEST_PATH . '/bin/filesystem/read/foo',
    Demo\TEST_PATH . '/bin/filesystem/read/lorem',
];

Demo\assert_true(
    !array_diff($expected_array, $array),
    "Unexpected result"
);
