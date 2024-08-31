<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Model\SharedAmounts\RequiredCountSharedAmount;
use LWP\Components\Model\SharedAmounts\Exceptions\SharedAmountOutOfBoundsException;

$required_count_shared_amount = new RequiredCountSharedAmount(RequiredCountSharedAmount::AT_LEAST_ONE);

/* Initial State */

echo "Is initial state invalid? ";
var_dump($required_count_shared_amount->isInInvalidState());

echo "Initial state message: ";
var_dump($required_count_shared_amount->getLastViolation()->getErrorMessageString());


/* Increment and Decrement */

try {

    $i1 = $required_count_shared_amount->add();
    $required_count_shared_amount->remove($i1);

} catch (SharedAmountOutOfBoundsException) {

    if (!$required_count_shared_amount->isInPursue()) {
        prl("Expected error (not in pursue): " . $required_count_shared_amount->getLastViolation()->getErrorMessageString());
    }
}

echo "Final value: ";
var_dump($required_count_shared_amount->getValue());

echo "Is final state invalid: ";
var_dump($required_count_shared_amount->isInInvalidState());
