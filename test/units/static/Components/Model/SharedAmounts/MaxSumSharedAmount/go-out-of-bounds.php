<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Model\SharedAmounts\MaxSumSharedAmount;
use LWP\Components\Model\SharedAmounts\Exceptions\SharedAmountOutOfBoundsException;

$max_sum_shared_amount = new MaxSumSharedAmount(100);
$result = null;

try {
    $identifier1 = $max_sum_shared_amount->add(70);
    $identifier2 = $max_sum_shared_amount->add(60);
    $result = false;
} catch (SharedAmountOutOfBoundsException) {
    $result = true;
}

Demo\assert_true($result, "Max sum shared amount did not go out of bounds");
