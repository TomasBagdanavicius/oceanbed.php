<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\DataTypes\Natural\String\ToStringDataTypeConverter;

// Data type conversion.

$string_data_type_value_container = ToStringDataTypeConverter::convert("12345");

$enhanced_property = new EnhancedProperty(
    'prop_1',
    'integer',
    $string_data_type_value_container
);

Demo\assert_true(
    true,
    "Value has not been properly converted"
);
