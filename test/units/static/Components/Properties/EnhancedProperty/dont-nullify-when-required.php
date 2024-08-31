<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\Properties\Exceptions\PropertyValueNotAvailableException;

// Don't nullify when required.

$enhanced_property = new EnhancedProperty(
    'prop_1',
    'string',
    required: true,
    nullable: true
);

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
