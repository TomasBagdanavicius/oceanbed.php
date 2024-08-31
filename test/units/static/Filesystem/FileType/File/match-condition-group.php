<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\FileType\File;
use LWP\Filesystem\Path\PathEnvironmentRouter;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;

$pathname = realpath(Demo\TEST_PATH . '/bin/filesystem/static/files/hello-world.txt');
$path_handler = PathEnvironmentRouter::getStaticInstance();
$file_path = $path_handler::getFilePathInstance($pathname);
$file = new File($file_path);

$condition = new Condition('basename', 'hello-world.txt', ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_group = ConditionGroup::fromCondition($condition);
$condition_group->add(new Condition('size', 12));

Demo\assert_true(
    $file->matchConditionGroup($condition_group),
    "File incorrectly matches given condition group"
);
