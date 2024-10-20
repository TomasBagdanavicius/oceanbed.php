<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Conditions\Condition;
use LWP\Common\Enums\AssortmentEnum;
use LWP\Components\Attributes\NoValueAttribute;

$condition = new Condition('foo', new NoValueAttribute(), AssortmentEnum::EXCLUDE);

Demo\assert_true(
    $condition->matchKeyword('bar') === true,
    "Unexpected result"
);
