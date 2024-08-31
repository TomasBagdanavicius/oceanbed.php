<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Violations\DivisibleByViolation;

$division_rule = 10;
$test_number = 25;

if (($test_number % $division_rule) !== 0) {

    $divisible_by_violation = new DivisibleByViolation($division_rule, $test_number);

    prl($divisible_by_violation->getErrorMessageString());
    #prl( $divisible_by_violation->getExtendedErrorMessageString() );
    #prl( $divisible_by_violation->getErrorMessage()->text );
    print_r($divisible_by_violation->getCorrectionOpportunities());
    #$divisible_by_violation->throwException();

} else {

    prl("OK");
}
