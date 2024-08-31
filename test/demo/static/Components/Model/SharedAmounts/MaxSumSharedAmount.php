<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Definitions\MaxSumDefinition;
use LWP\Components\Model\SharedAmounts\MaxSumSharedAmount;
use LWP\Components\Model\SharedAmounts\Exceptions\SharedAmountOutOfBoundsException;

$max_sum_shared_amount = new MaxSumSharedAmount(100);

echo "Is in invalid state: ";
var_dump($max_sum_shared_amount->isInInvalidState());

try {

    $i1 = $max_sum_shared_amount->add(70);
    // Subtract.
    $max_sum_shared_amount->remove($i1);
    $i2 = $max_sum_shared_amount->add(60, 'custom_identifier');
    // Subtract.
    $max_sum_shared_amount->remove($i2);
    $i3 = $max_sum_shared_amount->add(110);

} catch (SharedAmountOutOfBoundsException $exception) {

    prl("Expected error: " . $exception->getMessage() . ". " . $exception->getPrevious()->getMessage());
}

/* From Definition */

$max_sum_shared_amount = MaxSumSharedAmount::fromDefinition(new MaxSumDefinition(100));
echo "From definition: ";
var_dump($max_sum_shared_amount::class);
