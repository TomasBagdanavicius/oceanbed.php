<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\DataTypes\Natural\Null\NullDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeConversionException;

// Don't accept null data type container.

$expected_thrown = false;

try {

    $enhanced_property = new EnhancedProperty(
        'prop_1',
        'string',
        new NullDataTypeValueContainer(),
        nullable: false
    );

} catch (DataTypeConversionException $exception) {

    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Expected DataTypeConversionException not thrown"
);
