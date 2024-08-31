<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Model\SharedAmounts\RangeCountSharedAmount;

$range_count_shared_amount = new RangeCountSharedAmount(
    min_sum: 5,
    max_sum: 10
);

Demo\assert_true($range_count_shared_amount->getState() === RangeCountSharedAmount::STATE_UNDERFLOW, "Unexpected result");
