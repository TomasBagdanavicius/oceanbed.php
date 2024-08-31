<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Constraints\CharsetConstraint;
use LWP\Components\Violations\CharsetViolation;

$charset_constraint = new CharsetConstraint('ascii');

echo "Constaint value: ";
var_dump($charset_constraint->getValue());

echo "Definition: ";
var_dump($charset_constraint->getDefinition());

$validator = $charset_constraint->getValidator();
$validator_result = $validator->validate("Ąžuolo gilė");

if ($validator_result === true) {

    prl("OK");

} else {

    prl("Violation detected");

    $violation = $validator_result;

    echo "Is instance of CharsetViolation: ";
    var_dump($violation instanceof CharsetViolation);

    echo "Error message: ";
    var_dump($violation->getErrorMessage()->text);
}
