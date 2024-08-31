<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Model\SharedAmounts\RangeCountSharedAmount;
use LWP\Components\Model\SharedAmounts\Exceptions\SharedAmountOutOfBoundsException;
use LWP\Components\Model\SharedAmounts\Exceptions\SharedAmountInvalidStateException;

$range_count_shared_amount = new RangeCountSharedAmount(
    min_sum: 5,
    max_sum: 10,
    options: (RangeCountSharedAmount::THROW_ON_ACTION | RangeCountSharedAmount::THROW_WHEN_IN_PURSUE)
);

/* Initial State */

echo "Initial state integer: ";
var_dump($range_count_shared_amount->getState());
echo "Is initial state invalid? ";
var_dump($range_count_shared_amount->isInInvalidState());

if ($range_count_shared_amount->getState() === RangeCountSharedAmount::STATE_UNDERFLOW) {

    prl("Initial state is \"underflow\".");

    try {
        $range_count_shared_amount->throwException();
    } catch (SharedAmountInvalidStateException $exception) {
        prl("Expected error: " . $exception->getMessage() . " " . $exception->getPrevious()->getMessage());
    }
}

/* Increment and Decrement */

try {

    $range_count_shared_amount->add(2);

} catch (SharedAmountOutOfBoundsException $exception) {

    prl("Expected error: " . $exception->getMessage() . ". " . $exception->getPrevious()->getMessage());

    // Is in pursue.
    if ($range_count_shared_amount->isInPursue()) {
        prl("Is in pursue.");
    }
}

try {

    $range_count_shared_amount->add(3);

} catch (SharedAmountOutOfBoundsException $exception) {

    // No error is expected.
}

try {

    $range_count_shared_amount->remove(1);

} catch (SharedAmountOutOfBoundsException $exception) {

    prl("Expected error: " . $exception->getMessage() . ". " . $exception->getPrevious()->getMessage());

    // Is in pursue.
    if (!$range_count_shared_amount->isInPursue()) {
        die("Is not in pursue.");
    }
}
