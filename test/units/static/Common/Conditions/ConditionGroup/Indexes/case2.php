<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(Demo\TEST_PATH . '/units/shared/definition-array-people.php');

use LWP\Common\Array\IndexableArrayCollection;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;

$indexable_array = new IndexableArrayCollection($definition_array);
$index_tree = $indexable_array->getIndexTree();
#pre( $index_tree );

$condition_1 = new Condition('name', 'John', ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_2 = new Condition('occupation', 'teacher', ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_3 = new Condition('sex', 'female', ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_4 = new Condition('age', 20, ConditionComparisonOperatorsEnum::EQUAL_TO);

$condition_group_1 = new ConditionGroup();
$condition_group_1->add($condition_2);
$condition_group_1->add($condition_3, NamedOperatorsEnum::AND);

$condition_group_root = new ConditionGroup();
$condition_group_root->add($condition_1);
$condition_group_root->add($condition_group_1, NamedOperatorsEnum::OR);
$condition_group_root->add($condition_4, NamedOperatorsEnum::AND);

include(__DIR__ . '/../../../../../shared/condition-group-indexes-matching.php');

Demo\assert_true(
    $reactive_matches == $index_matches,
    "Reactive matches "
    . implode(',', $reactive_matches)
    . " are not equal to index matches "
    . implode(',', $index_matches)
);
