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
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;

$pathname = getLocationInFilesystemBin('static/files/hello-world.txt');
$path_handler = PathEnvironmentRouter::getStaticInstance();
$file_path = $path_handler::getFilePathInstance($pathname);
$file = new File($file_path);
$condition = new Condition('basename', 'hello-world.txt', ConditionComparisonOperatorsEnum::EQUAL_TO);

Demo\assert_true(
    $file->matchCondition($condition),
    "File incorrectly matches single condition"
);
