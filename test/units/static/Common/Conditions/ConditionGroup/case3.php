<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(__DIR__ . '/../../../../shared/validate-condition.php');

# var_dump( t(1) and f(2) and (t(3) or f(4)) and t(5) );

use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Enums\NamedOperatorsEnum;

$condition_1 = new Condition('one', 1, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_2 = new Condition('two', 20, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_3 = new Condition('three', 3, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_4 = new Condition('four', 40, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_5 = new Condition('five', 5, ConditionComparisonOperatorsEnum::EQUAL_TO);

$condition_group_1 = new ConditionGroup();
$condition_group_1->add($condition_3);
$condition_group_1->add($condition_4, NamedOperatorsEnum::OR);

$condition_group_root = new ConditionGroup();
$condition_group_root->add($condition_1);
$condition_group_root->add($condition_2, NamedOperatorsEnum::AND);
$condition_group_root->add($condition_group_1, NamedOperatorsEnum::AND);
$condition_group_root->add($condition_5, NamedOperatorsEnum::AND);
$condition_group_root->setFlags($condition_group_root::DEBUG_MODE);

$result = $condition_group_root->reactiveMatch(validateCondition(...));
$order_numbers = $condition_group_root->getExecConditionPositions();

$expected = [
    false,
    [1, 2],
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
