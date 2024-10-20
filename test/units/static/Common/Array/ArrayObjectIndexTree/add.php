<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Array\ArrayObjectIndexTree;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;

$index_tree = new ArrayObjectIndexTree();

$index_id_1 = $index_tree->add([
    'id' => 1,
    'msg' => 'There has been an error.',
    'type' => 'error',
    'origin' => 'session',
], 'index1');

$condition = new Condition('id', 1, ConditionComparisonOperatorsEnum::EQUAL_TO);

Demo\assert_true(
    $index_tree->assessCondition($condition) === ['index1' => 'index1'],
    "Unexpected result"
);
