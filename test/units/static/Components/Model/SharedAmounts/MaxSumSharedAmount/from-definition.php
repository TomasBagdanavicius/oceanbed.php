<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Definitions\MaxSumDefinition;
use LWP\Components\Model\SharedAmounts\MaxSumSharedAmount;

$max_sum_shared_amount = MaxSumSharedAmount::fromDefinition(new MaxSumDefinition(100));

Demo\assert_true(
    ($max_sum_shared_amount instanceof MaxSumSharedAmount)
    && $max_sum_shared_amount->max_sum === 100,
    "Unexpected result"
);
