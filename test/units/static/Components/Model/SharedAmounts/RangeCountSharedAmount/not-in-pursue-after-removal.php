<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Model\SharedAmounts\RangeCountSharedAmount;
use LWP\Components\Model\SharedAmounts\Exceptions\SharedAmountOutOfBoundsException;

$range_count_shared_amount = new RangeCountSharedAmount(
    min_sum: 5,
    max_sum: 10,
    options: (RangeCountSharedAmount::THROW_ON_ACTION | RangeCountSharedAmount::THROW_WHEN_IN_PURSUE)
);
try {
    $identifier1 = $range_count_shared_amount->add(5);
    $range_count_shared_amount->add($identifier1);
} catch (SharedAmountOutOfBoundsException) {
    // Continue
}

Demo\assert_true($range_count_shared_amount->isInPursue() === false, "Range incorrectly indicated that it was in pursue");
