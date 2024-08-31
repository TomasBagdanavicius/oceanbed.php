<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Violations\VariableTypeViolation;

$set = [
    'integer',
    'double',
];

$value = 'string';

// Array intersection is also supported.
if (!in_array($value, $set)) {

    $variable_type_violation = new VariableTypeViolation($set, $value, $value);

    prl($variable_type_violation->getErrorMessageString());
    #prl( $variable_type_violation->getExtendedErrorMessageString() );
    #prl( $variable_type_violation->getErrorMessage()->text );
    print_r($variable_type_violation->getCorrectionOpportunities());
    #$variable_type_violation->throwException();

} else {

    prl("OK");
}
