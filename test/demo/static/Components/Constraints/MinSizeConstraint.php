<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Constraints\MinSizeConstraint;
use LWP\Components\Constraints\MaxSizeConstraint;
use LWP\Components\Violations\MinSizeViolation;

$min_size_constraint = new MinSizeConstraint(10);

echo "Constaint value: ";
var_dump($min_size_constraint->getValue());

#var_dump( $min_size_constraint->getDefinition() );

$validator = $min_size_constraint->getValidator();
$validator_result = $validator->validate(9);

if ($validator_result === true) {

    prl("OK");

} else {

    prl("Violation detected");

    echo "Is instance of MinSizeViolation: ";
    var_dump($validator_result instanceof MinSizeViolation);

    $violation = $validator_result;

    echo "Offset: ";
    var_dump($violation->getOffset());

    echo "Error message: ";
    var_dump($violation->getErrorMessageString());

    echo "Correction opportunities: ";
    var_dump($violation->getCorrectionOpportunities());
}

/* Collision Checking */

/* $max_size_constraint = new MaxSizeConstraint(9);

try {

    $min_size_constraint->collisionAssistance($max_size_constraint);

} catch( \Exception $exception ) {

    prl( "Expected error: " . $exception->getMessage() );
} */
