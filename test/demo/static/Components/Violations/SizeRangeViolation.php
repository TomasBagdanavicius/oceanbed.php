<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Violations\SizeRangeViolation;

$range = ['min' => 1, 'max' => 100];
$test_number = 101;

if ($test_number < $range['min'] || $test_number > $range['max']) {

    $size_range_violation = new SizeRangeViolation($range['min'], $range['max'], $test_number);

    prl($size_range_violation->getErrorMessageString());
    #print_r( $size_range_violation->getCorrectionOpportunities() );
    #echo "Offset: "; var_dump( $size_range_violation->getOffset() );
    #echo "Is underflow: "; var_dump( $size_range_violation->isUnderflow() );
    #echo "Is overflow: "; var_dump( $size_range_violation->isOverflow() );
    #$size_range_violation->throwException();

} else {

    prl("OK");
}
