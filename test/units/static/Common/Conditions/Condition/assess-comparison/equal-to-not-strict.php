<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;

$comparison = Condition::assessComparisonOperator(1, '1', ConditionComparisonOperatorsEnum::EQUAL_TO, strict_type: false);

Demo\assert_true(
    $comparison === true,
    "Unexpected result"
);
