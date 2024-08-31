<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Conditions\Condition;
use LWP\Common\Enums\AssortmentEnum;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Components\Attributes\NoValueAttribute;

$stringify_replacer = function (Condition $condition): ?string {
    return sprintf("%s %s %s", "'" . $condition->keyword . "'", $condition->control_operator->value, $condition->value);
};

$condition = new Condition('foo', 1, ConditionComparisonOperatorsEnum::EQUAL_TO);
#$condition = new Condition('foo', 1, ConditionComparisonOperatorsEnum::EQUAL_TO, stringify_replacer: $stringify_replacer);

echo "To string: ";
var_dump($condition->__toString());
echo "Match: ";
var_dump($condition->match('foo', '1'));

/* Case Insensitive */

$condition = new Condition('foo', 'abcde', ConditionComparisonOperatorsEnum::EQUAL_TO, case_sensitive: false);
echo "Case insensitive: ";
var_dump($condition->match('foo', 'ABCde'));

/* Accent Insensitive */
$condition = new Condition('foo', 'abcde', ConditionComparisonOperatorsEnum::EQUAL_TO, accent_sensitive: false);
echo "Accent insensitive: ";
var_dump($condition->match('foo', 'ąbčdė'));

/* No Value Condition */

$condition = new Condition('foo', new NoValueAttribute(), AssortmentEnum::EXCLUDE);
echo "To string: ";
var_dump($condition->__toString());
echo "Match keyword: ";
var_dump($condition->matchKeyword('bar'));

/* No Keyword Condition */

$condition = new Condition(new NoValueAttribute(), '1', AssortmentEnum::INCLUDE);
echo "To string: ";
var_dump($condition->__toString());
echo "Match value: ";
var_dump($condition->matchValue('1'));

/* Assess Comparison Operator */

echo "Assess comparison: ";
var_dump(Condition::assessComparisonOperator(1, '1', ConditionComparisonOperatorsEnum::EQUAL_TO, strict_type: false));
