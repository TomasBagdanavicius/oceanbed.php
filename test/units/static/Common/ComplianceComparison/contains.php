<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\ComplianceComparison;

$test_cases = [
    // Case-sensitive tests
    ['input' => 'abcde', 'compare' => 'bcd', 'case_sensitive' => true, 'accent_sensitive' => true, 'expected' => true],
    ['input' => 'abcde', 'compare' => 'bCD', 'case_sensitive' => true, 'accent_sensitive' => true, 'expected' => false],
    // Case-insensitive tests
    ['input' => 'abcde', 'compare' => 'bCD', 'case_sensitive' => false, 'accent_sensitive' => true, 'expected' => true],
    ['input' => 'abcde', 'compare' => 'bČD', 'case_sensitive' => false, 'accent_sensitive' => true, 'expected' => false],
    // Case-insensitive and accent-insensitive tests
    ['input' => 'abcde', 'compare' => 'bČD', 'case_sensitive' => false, 'accent_sensitive' => false, 'expected' => true],
    // Array input tests (case-sensitive)
    ['input' => ['abcde'], 'compare' => 'abcde', 'case_sensitive' => true, 'accent_sensitive' => true, 'expected' => true],
    ['input' => ['abcde'], 'compare' => 'ABCde', 'case_sensitive' => true, 'accent_sensitive' => true, 'expected' => false],
    // Array input tests (case-insensitive)
    ['input' => ['abcde'], 'compare' => 'ABCde', 'case_sensitive' => false, 'accent_sensitive' => true, 'expected' => true],
    ['input' => ['abcde'], 'compare' => 'ĄBČdę', 'case_sensitive' => false, 'accent_sensitive' => true, 'expected' => false],
    // Array input tests (case-insensitive and accent-insensitive)
    ['input' => ['abcde'], 'compare' => 'ĄBČdę', 'case_sensitive' => false, 'accent_sensitive' => false, 'expected' => true],
    // Numeric tests (strict_type: false)
    ['input' => 123.45, 'compare' => 45, 'case_sensitive' => true, 'accent_sensitive' => true, 'strict_type' => false, 'expected' => true],
    ['input' => 123.45, 'compare' => '23', 'case_sensitive' => true, 'accent_sensitive' => true, 'strict_type' => false, 'expected' => true],
    ['input' => 123.45, 'compare' => 3.45, 'case_sensitive' => true, 'accent_sensitive' => true, 'strict_type' => false, 'expected' => true],
];

$all_tests_passed = true;
$error_message = "";

foreach ($test_cases as $key => $case) {
    $compliance_comparison = new ComplianceComparison(
        $case['input'],
        case_sensitive: $case['case_sensitive'] ?? true,
        accent_sensitive: $case['accent_sensitive'] ?? true,
        strict_type: $case['strict_type'] ?? true
    );

    $result = $compliance_comparison->contains($case['compare']);

    if ($result !== $case['expected']) {
        $all_tests_passed = false;
        $case_number = $key + 1;
        $expected = var_export($case['expected'], true);
        $actual = var_export($result, true);
        $error_message = "Error in case $case_number: expected $expected, got $actual";
        break;
    }
}

// Assert based on the result of the loop
Demo\assert_true($all_tests_passed, $error_message);
