<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Model\SharedAmounts\RequiredCountSharedAmount;
use LWP\Components\Model\SharedAmounts\Exceptions\SharedAmountOutOfBoundsException;

$required_count_shared_amount = new RequiredCountSharedAmount(RequiredCountSharedAmount::AT_LEAST_ONE);
$result = null;
try {
    $identifier = $required_count_shared_amount->add();
    $required_count_shared_amount->remove($identifier);
    $result = false;
} catch (SharedAmountOutOfBoundsException) {
    $result = true;
}

Demo\assert_true($result, "Required count shared amount incorrectly was left in bounds");
