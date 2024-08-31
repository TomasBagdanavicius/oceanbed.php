<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Model\SharedAmounts\RequiredCountSharedAmount;
use LWP\Components\Model\SharedAmounts\Exceptions\SharedAmountOutOfBoundsException;

$required_count_shared_amount = new RequiredCountSharedAmount(1);
$result = null;
try {
    $required_count_shared_amount->add();
    $result = true;
} catch (SharedAmountOutOfBoundsException) {
    $result = false;
}

Demo\assert_true($result, "Required count shared amount incorrectly went out of bounds");
