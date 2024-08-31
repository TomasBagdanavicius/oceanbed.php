<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\BaseProperty;
use LWP\Components\DataTypes\{
    Natural\Null\NullDataTypeValueContainer,
    Exceptions\DataTypeConversionException
};

// Incompatible default value data type.

$expected_thrown = false;

try {

    $base_property = new BaseProperty(
        'prop1',
        'string',
        new NullDataTypeValueContainer()
    );

} catch (DataTypeConversionException $exception) {

    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Expected incompatible data type conversion error not thrown"
);
