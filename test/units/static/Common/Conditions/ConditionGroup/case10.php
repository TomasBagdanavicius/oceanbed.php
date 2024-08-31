<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(__DIR__ . '/../../../../shared/validate-condition.php');

# var_dump( (f(1) or f(2) and t(3) or (((t(4) and f(5) and t(6)) and t(7) or ((f(8) and f(9)) or t(10)) and f(11) and f(12)) or t(13)) or t(14)) and f(15) or t(16) );

use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Enums\NamedOperatorsEnum;

$condition_1 = new Condition('one', 10, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_2 = new Condition('two', 20, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_3 = new Condition('three', 3, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_4 = new Condition('four', 4, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_5 = new Condition('five', 50, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_6 = new Condition('six', 6, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_7 = new Condition('seven', 7, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_8 = new Condition('eight', 80, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_9 = new Condition('nine', 90, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_10 = new Condition('ten', 10, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_11 = new Condition('eleven', 110, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_12 = new Condition('twelve', 120, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_13 = new Condition('thirteen', 13, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_14 = new Condition('fourteen', 14, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_15 = new Condition('fifteen', 150, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_16 = new Condition('sixteen', 16, ConditionComparisonOperatorsEnum::EQUAL_TO);

$condition_group_1 = new ConditionGroup();
$condition_group_1->add($condition_4);
$condition_group_1->add($condition_5, NamedOperatorsEnum::AND);
$condition_group_1->add($condition_6, NamedOperatorsEnum::AND);

$condition_group_2 = new ConditionGroup();
$condition_group_2->add($condition_8);
$condition_group_2->add($condition_9, NamedOperatorsEnum::AND);

$condition_group_3 = new ConditionGroup();
$condition_group_3->add($condition_group_2);
$condition_group_3->add($condition_10, NamedOperatorsEnum::OR);

$condition_group_4 = new ConditionGroup();
$condition_group_4->add($condition_group_1);
$condition_group_4->add($condition_7, NamedOperatorsEnum::AND);
$condition_group_4->add($condition_group_3, NamedOperatorsEnum::OR);
$condition_group_4->add($condition_11, NamedOperatorsEnum::AND);
$condition_group_4->add($condition_12, NamedOperatorsEnum::AND);

$condition_group_5 = new ConditionGroup();
$condition_group_5->add($condition_group_4, NamedOperatorsEnum::OR);
$condition_group_5->add($condition_13, NamedOperatorsEnum::OR);

$condition_group_6 = new ConditionGroup();
$condition_group_6->add($condition_1);
$condition_group_6->add($condition_2, NamedOperatorsEnum::OR);
$condition_group_6->add($condition_3, NamedOperatorsEnum::AND);
$condition_group_6->add($condition_group_5, NamedOperatorsEnum::OR);
$condition_group_6->add($condition_14, NamedOperatorsEnum::OR);

$condition_group_root = new ConditionGroup();
$condition_group_root->add($condition_group_6);
$condition_group_root->add($condition_15, NamedOperatorsEnum::AND);
$condition_group_root->add($condition_16, NamedOperatorsEnum::OR);

$condition_group_root->setFlags($condition_group_root::DEBUG_MODE);

$result = $condition_group_root->reactiveMatch(validateCondition(...));
$order_numbers = $condition_group_root->getExecConditionPositions();

$expected = [
    true,
    [1,2,4,5,8,10,11,13,15,16],
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
