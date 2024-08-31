<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Exceptions\ReadOnlyException;
use LWP\Components\Properties\BaseProperty;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;

// Readonly restriction.

$base_property = new BaseProperty(
    'prop1',
    'string',
    // Has default value
    new StringDataTypeValueContainer("Hello World!"),
    readonly: true
);

$expected_thrown = false;

try {
    $base_property->setValue("Hi there!");
} catch (ReadOnlyException) {
    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Expected ReadOnlyException not thrown"
);
