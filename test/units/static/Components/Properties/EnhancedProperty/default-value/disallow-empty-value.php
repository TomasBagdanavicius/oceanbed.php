<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;

// Disallow empty value.

$expected_thrown = false;

try {

    $enhanced_property = new EnhancedProperty(
        'prop1',
        'string',
        new StringDataTypeValueContainer(''),
        allow_empty: false
    );

} catch (\RuntimeException $exception) {

    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Expected exception not thrown"
);
