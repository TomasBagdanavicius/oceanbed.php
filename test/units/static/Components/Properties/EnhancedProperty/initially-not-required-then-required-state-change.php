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

try {
    $enhanced_property->setValue("Hello World!");
} catch (PropertyStateException $exception) {
    // Continue.
}

$enhanced_property->setRequired(true, release_parked_value: true);

if ($enhanced_property->hasParkedValue() === true) {
    throw new Error("Parked value should have been cleared");
}

if ($enhanced_property->isRequired() !== true) {
    throw new Error("Property should be required");
}

Demo\assert_true(
    $enhanced_property->getValue() === "Hello World!",
    "Incorrect value"
);
