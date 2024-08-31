<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Constraints\InSetConstraint;
use LWP\Components\Constraints\Validators\InSetConstraintValidator;
use LWP\Components\Violations\InSetViolation;

$set = [
    'one',
    'two',
    'three',
];

$in_set_constraint = new InSetConstraint($set);

#print_r( $in_set_constraint->getSet() );

$validator = new InSetConstraintValidator($in_set_constraint);

$validate = $validator->validate(['one', 'four']);

if ($validate === true) {

    prl("OK");

} else {

    print "Violation" . "\n";

    var_dump($validate instanceof InSetViolation);

    $violation = $validate;

    var_dump($violation->getErrorMessage()->text);
}
