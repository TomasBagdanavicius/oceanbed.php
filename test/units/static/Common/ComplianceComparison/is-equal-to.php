<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\ComplianceComparison;

$test_cases = [
    ['input' => 'abcde', 'compare' => 'abcde', 'case_sensitive' => true, 'accent_sensitive' => true, 'expected' => true],
    ['input' => 'abcde', 'compare' => 'ABCde', 'case_sensitive' => true, 'accent_sensitive' => true, 'expected' => false],
    ['input' => 'abcde', 'compare' => 'ABCde', 'case_sensitive' => false, 'accent_sensitive' => true, 'expected' => true],
    ['input' => 'abcde', 'compare' => 'ABCdĘ', 'case_sensitive' => false, 'accent_sensitive' => true, 'expected' => false],
    ['input' => 'abcde', 'compare' => 'ABCdĘ', 'case_sensitive' => false, 'accent_sensitive' => false, 'expected' => true],
];

$all_tests_passed = true;
$error_message = "";

foreach ($test_cases as $key => $case) {
    $compliance_comparison = new ComplianceComparison(
        $case['input'],
        case_sensitive: $case['case_sensitive'],
        accent_sensitive: $case['accent_sensitive']
    );

    $result = $compliance_comparison->isEqualTo($case['compare']);

    if ($result !== $case['expected']) {
        $all_tests_passed = false;
        $case_number = $key + 1;
        $expected = var_export($case['expected'], true);
        $actual = var_export($result, true);
        $error_message = "Error in case $case_number: expected $expected, got $actual";
        break;
    }
}

Demo\assert_true($all_tests_passed, $error_message);
