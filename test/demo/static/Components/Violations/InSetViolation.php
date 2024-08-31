<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Violations\InSetViolation;

$set = [
    'one',
    'two',
    'three',
];
$value = 'four';

// Array intersection is also supported.
if (!in_array($value, $set)) {

    $in_set_violation = new InSetViolation($set, $value, $value);

    #$in_set_violation->setErrorMessageString(sprintf("The following elements were not found: %s.", InSetViolation::getArrayValuesAsQuotedStrings((array)$in_set_violation->missing_values)));
    prl($in_set_violation->getErrorMessageString());
    #prl( $in_set_violation->getExtendedErrorMessageString() );
    #prl( $in_set_violation->getErrorMessage()->text );
    #print_r( $in_set_violation->getCorrectionOpportunities() );
    #$in_set_violation->throwException();

} else {

    prl("OK");
}
