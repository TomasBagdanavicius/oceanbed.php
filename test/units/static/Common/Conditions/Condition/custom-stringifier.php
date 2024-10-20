<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;

$stringify_replacer = function (Condition $condition): ?string {
    return sprintf("'%s' %s '%s'", $condition->keyword, $condition->control_operator->value, $condition->value);
};

$condition = new Condition('foo', 1, ConditionComparisonOperatorsEnum::EQUAL_TO, stringify_replacer: $stringify_replacer);

Demo\assert_true(
    (string)$condition === "'foo' = '1'",
    "Unexpected result"
);
