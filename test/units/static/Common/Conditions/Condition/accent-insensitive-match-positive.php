<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;

$condition = new Condition('foo', 'abcde', ConditionComparisonOperatorsEnum::EQUAL_TO, accent_sensitive: false);

Demo\assert_true(
    $condition->match('foo', 'ąbčdė') === true,
    "Unexpected result"
);
