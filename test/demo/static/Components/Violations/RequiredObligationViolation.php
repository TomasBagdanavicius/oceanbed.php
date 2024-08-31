<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Violations\RequiredObligationViolation;

$obligation = true;
$value = false;

if ($obligation != $value) {

    $required_obligation_violation = new RequiredObligationViolation($obligation, $value);

    prl($required_obligation_violation->getErrorMessageString());
    #prl( $required_obligation_violation->getExtendedErrorMessageString() );
    #prl( $required_obligation_violation->getErrorMessage()->text );
    print_r($required_obligation_violation->getCorrectionOpportunities());
    #var_dump( $required_obligation_violation->getOffset() );
    #$required_obligation_violation->throwException();

} else {

    prl("OK");
}
