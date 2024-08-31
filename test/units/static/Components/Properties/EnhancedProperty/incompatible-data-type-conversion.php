<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\DataTypes\Exceptions\DataTypeConversionException;
use LWP\Components\DataTypes\Natural\String\ToStringDataTypeConverter;

// Incompatible data type conversion.

// Mind the leading "a" character.
$string_data_type_value_container
    = ToStringDataTypeConverter::convert("a12345");

$expected_thrown = false;

try {

    $enhanced_property = new EnhancedProperty(
        'prop_1',
        'integer',
        $string_data_type_value_container
    );

} catch (DataTypeConversionException $exception) {

    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Expected DataTypeConversionException not thrown"
);
