<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Constraints\SizeRangeConstraint;
use LWP\Components\Constraints\Violations\SizeRangeConstraintViolation;

$constraint = new SizeRangeConstraint(1, 100);

var_dump($constraint->getValue());
var_dump($constraint->getMinSize());
var_dump($constraint->getMaxSize());

$validator = $constraint->getValidator();
$validation_result = $validator->validate(101);

if ($validation_result !== true) {

    prl("Violation");

    $violation = $validation_result;

    var_dump($violation instanceof SizeRangeConstraintViolation);

    var_dump($violation->getOffset());
    var_dump($violation->getErrorMessage()->text);
    var_dump($violation->getCorrectionOpportunities());

} else {

    prl("OK");
}
