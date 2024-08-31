<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Model\SharedAmounts\RangeCountSharedAmount;

$range_count_shared_amount = new RangeCountSharedAmount(
    // Zero allows the initial state to be valid
    min_sum: 0,
    max_sum: 10
);

Demo\assert_true($range_count_shared_amount->isInValidState(), "Unexpected result");
