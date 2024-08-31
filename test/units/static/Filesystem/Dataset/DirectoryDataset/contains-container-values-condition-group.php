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
use LWP\Common\Conditions\ConditionGroup;

$pathname = realpath(Demo\TEST_PATH . '/bin/filesystem/read');
$file_path = PosixPath::getFilePathInstance($pathname);
$directory = new Directory($file_path);
$dataset = new FilesystemDirectoryDataset($directory);
$condition_group = ConditionGroup::fromCondition(new Condition('size', 5));
$contains = $dataset->containsContainerValues('basename', [
    'abc.txt',
    'abcde.txt',
], $condition_group);

Demo\assert_true(
    $contains === [
        'abcde.txt'
    ],
    "Unexpected result"
);
