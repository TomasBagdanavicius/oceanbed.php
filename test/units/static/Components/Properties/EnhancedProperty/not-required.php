<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\{
    EnhancedProperty,
    Exceptions\PropertyStateException
};

// Initially not required - then required state change.

$enhanced_property = new EnhancedProperty(
    'title',
    'string',
    required: false
);

$expected_thrown = false;

try {
    $enhanced_property->setValue("Hello World!");
} catch (PropertyStateException $exception) {
    $expected_thrown = true;
}

Demo\assert_true(
    (
        $expected_thrown
        && $enhanced_property->hasParkedValue() === true
    ),
    "Not required property incorrectly handled"
);
