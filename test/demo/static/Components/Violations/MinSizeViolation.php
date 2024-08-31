<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Violations\MinSizeViolation;

$min_size = 10;
$test_number = 7;

if ($test_number < $min_size) {

    $min_size_violation = new MinSizeViolation($min_size, $test_number);

    prl($min_size_violation->getErrorMessageString());
    #prl( $min_size_violation->getExtendedErrorMessageString() );
    #prl( $min_size_violation->getErrorMessage()->text );
    print_r($min_size_violation->getCorrectionOpportunities());
    #var_dump( $min_size_violation->getOffset() );
    #$min_size_violation->throwException();

} else {

    prl("OK");
}
