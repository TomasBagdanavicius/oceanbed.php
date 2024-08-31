<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Conditions\ConditionMatchFilterIterator;
use LWP\Filesystem\FileType\DirectoryReader;
use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Path\PosixPath;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Enums\NamedOperatorsEnum;

$file_path = PosixPath::getFilePathInstance(realpath(Demo\TEST_PATH . '/bin/filesystem/read'));
$directory = new Directory($file_path);

$iterator = new DirectoryReader($directory);

$condition = new Condition('basename', 'file-1.txt');
$condition_group = ConditionGroup::fromCondition($condition);
$condition_group->add(new Condition('type', 'directory'), NamedOperatorsEnum::OR);

prl($condition_group->__toString());

$iterator = new ConditionMatchFilterIterator($iterator, $condition_group);

foreach ($iterator as $file) {

    echo $file->pathname . ' ' . $file::class . PHP_EOL;
}
