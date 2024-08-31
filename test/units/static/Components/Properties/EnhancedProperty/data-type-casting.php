<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\DataTypes\Natural\Integer\ToIntegerDataTypeConverter;

// Data type casting.

$integer_data_type_value_container
    = ToIntegerDataTypeConverter::convert(12345);

$enhanced_property = new EnhancedProperty(
    'prop_1',
    'string',
    $integer_data_type_value_container
);

Demo\assert_true(
    $enhanced_property->getValue() === "12345",
    "Incorrect value"
);
