<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Constraints\MaxSizeConstraint;
use LWP\Components\Violations\MaxSizeViolation;
use LWP\Components\Constraints\MinSizeConstraint;

$max_size_constraint = new MaxSizeConstraint(10);

echo "Constraint value: ";
var_dump($max_size_constraint->getValue());

/* echo "Definition: ";
var_dump( $min_size_constraint->getDefinition() ); */

$validator = $max_size_constraint->getValidator();
$validator_result = $validator->validate(11);

if ($validator_result === true) {

    prl("OK");

} else {

    prl("Violation detected");

    $violation = $validator_result;

    echo "Is instance of MaxSizeViolation: ";
    var_dump($violation instanceof MaxSizeViolation);

    echo "Offset: ";
    var_dump($violation->getOffset());

    echo "Error message: ";
    var_dump($violation->getErrorMessage()->text);

    echo "Correction opportunities: ";
    var_dump($violation->getCorrectionOpportunities());
}

/* Collision Checking */

/* $min_size_constraint = new MinSizeConstraint(11);

try {
    $max_size_constraint->collisionAssistance($min_size_constraint);
} catch( \RangeException $exception ) {
    prl( "Expected error: " . $exception->getMessage() );
} */
