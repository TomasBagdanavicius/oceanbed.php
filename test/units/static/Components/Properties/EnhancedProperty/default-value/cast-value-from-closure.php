<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;

// Cast value from closure.

$enhanced_property = new EnhancedProperty(
    'prop1',
    'string',
    function (EnhancedProperty $property): int {
        return 100;
    }
);

Demo\assert_true(
    // Must be converted to string and of the same value.
    $enhanced_property->getValue() === "100",
    "Incorrect value"
);
