<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Constraints\VariableTypeConstraint;
use LWP\Components\Constraints\Validators\VariableTypeConstraintValidator;
use LWP\Components\Violations\VariableTypeViolation;

$type_constraint = new VariableTypeConstraint(['string', 'integer']);

echo "Value: ";
print_r($type_constraint->getValue());

$validator = new VariableTypeConstraintValidator($type_constraint);

$validate = $validator->validate(['one', 'two', 'three']);

if ($validate === true) {

    prl("OK");

} else {

    print "Violation" . "\n";

    var_dump($validate instanceof VariableTypeViolation);

    $violation = $validate;
}
