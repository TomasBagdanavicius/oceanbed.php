<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\{
    EnhancedProperty,
    Exceptions\PropertyValueContainsErrorsException,
    Exceptions\PropertyValueNotAvailableException
};
use LWP\Components\Constraints\MaxSizeConstraint;

// Value should not be available after errors encountered.

$enhanced_property = new EnhancedProperty('prop_1', 'string');

/* Set Constraints */

$max_size_constraint = new MaxSizeConstraint(12);
$enhanced_property->setConstraint($max_size_constraint);

try {
    $enhanced_property->setValue("Lorem ipsum dolor sit amet...");
} catch (PropertyValueContainsErrorsException $exception) {

}

$expected_thrown = false;

try {
    $value = $enhanced_property->getValue();
} catch (PropertyValueNotAvailableException $exception) {
    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Expected PropertyValueNotAvailableException not thrown"
);
