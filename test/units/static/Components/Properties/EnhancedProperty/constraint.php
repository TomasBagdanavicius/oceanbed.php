<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\{
    EnhancedProperty,
    Exceptions\PropertyValueContainsErrorsException
};
use LWP\Components\Constraints\MaxSizeConstraint;

// Constraints and value errors.

$enhanced_property = new EnhancedProperty('prop_1', 'integer');

$max_size_constraint = new MaxSizeConstraint(10);
$enhanced_property->setConstraint($max_size_constraint);

$expected_thrown = false;

try {
    $enhanced_property->setValue(20);
} catch (PropertyValueContainsErrorsException $exception) {
    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Expected PropertyValueContainsErrorsException not thrown"
);
