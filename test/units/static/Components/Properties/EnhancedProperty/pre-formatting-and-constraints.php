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
use LWP\Components\Rules\StringTrimFormattingRule;
use LWP\Components\Constraints\MaxSizeConstraint;

// Pre-formatting and constraints.

$enhanced_property = new EnhancedProperty('prop_1', 'string');

/* Set Formatting Rules */

$string_trim_formatting_rule = new StringTrimFormattingRule();
$enhanced_property->setFormattingRule(
    $string_trim_formatting_rule,
    EnhancedProperty::PHASE_PRE
);

/* Set Constraints */

$max_size_constraint = new MaxSizeConstraint(12);
$enhanced_property->setConstraint($max_size_constraint);

/* Set Value */

$accepted_value = $enhanced_property->setValue(" Hello World! ");

Demo\assert_true(
    $accepted_value === "Hello World!",
    "Incorrect accepted value"
);
