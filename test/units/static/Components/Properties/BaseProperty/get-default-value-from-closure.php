<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\BaseProperty;

// Get default value from closure.

$base_property = new BaseProperty(
    'prop1',
    'string',
    function (BaseProperty $property): string {
        return "Hello World!";
    }
);

Demo\assert_true(
    $base_property->getDefaultValue() === "Hello World!",
    "Incorrect default value"
);
