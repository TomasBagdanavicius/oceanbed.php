<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Violations\MaxSizeViolation;

$max_size = 10;
$test_number = 12;

if ($test_number > $max_size) {

    $max_size_violation = new MaxSizeViolation($max_size, $test_number);

    prl($max_size_violation->getErrorMessageString());
    #prl( $max_size_violation->getExtendedErrorMessageString() );
    #prl( $max_size_violation->getErrorMessage()->text );
    print_r($max_size_violation->getCorrectionOpportunities());
    #var_dump( $max_size_violation->getOffset() );
    #$max_size_violation->throwException();

} else {

    prl("OK");
}
