<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Array\ArrayObjectIndexTree;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Enums\NamedOperatorsEnum;

$index_tree = new ArrayObjectIndexTree([
    // Non-indexable keys.
    'msg',
]);

$dataset_1 = [
    'id' => 1,
    'msg' => 'There has been an error.',
    'type' => 'error',
    'origin' => 'session',
];

$index_id_1 = $index_tree->add($dataset_1); // This element is purposely deleted below.

$index_id_2 = $index_tree->add([
    'id' => 5,
    'msg' => 'There has been an issue.',
    'type' => 'notice', // This element is purposely updated below.
    'origin' => 'session',
], index_name: 'name_1'); // Custom index name is used.

$index_id_3 = $index_tree->add([
    'id' => 7,
    'msg' => 'A regular message here.',
    'type' => 'regular',
    'origin' => 'core',
]);

$index_id_4 = $index_tree->add([
    'id' => 8,
    'msg' => 'Another regular message here.',
    'type' => 'regular',
    'origin' => 'session',
]);

// Removes the first dataset.
$index_tree->removeData($index_id_1, $dataset_1);

// Updates a specific value only.
$index_tree->updateValue($index_id_2, 'type', 'warning');

#print_r( $index_tree->getTree() );

/* Assess Condition */

$condition = new Condition('origin', 'session', ConditionComparisonOperatorsEnum::EQUAL_TO);

print_r($index_tree->assessCondition($condition)); // Expected result: [name_1, 2]

/* Assess Condition Group */

$condition1 = new Condition('origin', 'core', ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition2 = new Condition('type', 'warning', ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition3 = new Condition('id', 8, ConditionComparisonOperatorsEnum::EQUAL_TO);

$condition_group = new ConditionGroup();
$condition_group->add($condition1);
$condition_group->add($condition2, NamedOperatorsEnum::OR);
$condition_group->add($condition3, NamedOperatorsEnum::AND);

print_r($index_tree->assessConditionGroup($condition_group)); // Expected result: [1]
