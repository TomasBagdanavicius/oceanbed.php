<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(__DIR__ . '/../../../../shared/validate-condition.php');

# var_dump( ((t(1) and t(2) or f(3))) and f(4) or t(5) and f(6) or (t(7) or f(8) and t(9)) and f(10) or (f(11) or t(12) and f(13)) );

use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Enums\NamedOperatorsEnum;

$condition_1 = new Condition('one', 1, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_2 = new Condition('two', 2, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_3 = new Condition('three', 30, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_4 = new Condition('four', 40, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_5 = new Condition('five', 5, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_6 = new Condition('six', 60, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_7 = new Condition('seven', 7, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_8 = new Condition('eight', 80, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_9 = new Condition('nine', 9, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_10 = new Condition('ten', 100, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_11 = new Condition('eleven', 110, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_12 = new Condition('twelve', 12, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_13 = new Condition('thirteen', 130, ConditionComparisonOperatorsEnum::EQUAL_TO);

$condition_group_1 = new ConditionGroup();
$condition_group_1->add($condition_1);
$condition_group_1->add($condition_2, NamedOperatorsEnum::AND);
$condition_group_1->add($condition_3, NamedOperatorsEnum::OR);

$condition_group_2 = new ConditionGroup();
$condition_group_2->add($condition_group_1);

$condition_group_3 = new ConditionGroup();
$condition_group_3->add($condition_7);
$condition_group_3->add($condition_8, NamedOperatorsEnum::OR);
$condition_group_3->add($condition_9, NamedOperatorsEnum::AND);

$condition_group_4 = new ConditionGroup();
$condition_group_4->add($condition_11);
$condition_group_4->add($condition_12, NamedOperatorsEnum::OR);
$condition_group_4->add($condition_13, NamedOperatorsEnum::AND);

$condition_group_root = new ConditionGroup();
$condition_group_root->add($condition_group_2);
$condition_group_root->add($condition_4, NamedOperatorsEnum::AND);
$condition_group_root->add($condition_5, NamedOperatorsEnum::OR);
$condition_group_root->add($condition_6, NamedOperatorsEnum::AND);
$condition_group_root->add($condition_group_3, NamedOperatorsEnum::OR);
$condition_group_root->add($condition_10, NamedOperatorsEnum::AND);
$condition_group_root->add($condition_group_4, NamedOperatorsEnum::OR);

$condition_group_root->setFlags($condition_group_root::DEBUG_MODE);

$result = $condition_group_root->reactiveMatch(validateCondition(...));
$order_numbers = $condition_group_root->getExecConditionPositions();

$expected = [
    false,
    [1,2,4,5,6,7,10,11,12,13],
];

Demo\assert_true(
    (
        $result === $expected[0]
        && $order_numbers === $expected[1]
    ),
    "Expected result: "
    . var_export($expected[0], true)
    . ", got "
    . var_export($result, true)
    . "; Expected order numbers: "
    . implode(',', $expected[1])
    . ", got "
    . implode(',', $order_numbers)
);
