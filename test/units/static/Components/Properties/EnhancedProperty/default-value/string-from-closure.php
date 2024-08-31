<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;

// String from closure.

$enhanced_property = new EnhancedProperty(
    'prop1',
    'string',
    function (EnhancedProperty $property): string {
        return "Hello World!";
    }
);

Demo\assert_true(
    // Must match the exact string.
    $enhanced_property->getValue() === "Hello World!",
    "Incorrect value"
);
