<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;

// Invalid data type from closure.

$expected_thrown = false;

try {

    $enhanced_property = new EnhancedProperty(
        'prop1',
        'string',
        function (EnhancedProperty $property): array {
            return [];
        }
    );

} catch (\RuntimeException $exception) {

    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Expected exception not thrown"
);
