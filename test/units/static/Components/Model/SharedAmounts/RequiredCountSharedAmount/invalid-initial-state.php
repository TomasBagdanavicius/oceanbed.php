<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Model\SharedAmounts\RequiredCountSharedAmount;

$required_count_shared_amount = new RequiredCountSharedAmount(RequiredCountSharedAmount::AT_LEAST_ONE);

Demo\assert_true($required_count_shared_amount->isInInvalidState(), "Required count indicated that it was not in invalid state");
