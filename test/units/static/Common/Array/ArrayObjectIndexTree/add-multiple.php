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
], index_name: 'index1');

$index_id_2 = $index_tree->add([
    'id' => 2,
    'msg' => 'There has been an issue.',
    'type' => 'notice',
    'origin' => 'session',
], index_name: 'index2');

$index_id_3 = $index_tree->add([
    'id' => 3,
    'msg' => 'A regular message here.',
    'type' => 'regular',
    'origin' => 'core',
], index_name: 'index3');

Demo\assert_true(
    $index_tree->dataIndexExists('index1')
        && $index_tree->dataIndexExists('index2')
        && $index_tree->dataIndexExists('index3'),
    "Unexpected result"
);
