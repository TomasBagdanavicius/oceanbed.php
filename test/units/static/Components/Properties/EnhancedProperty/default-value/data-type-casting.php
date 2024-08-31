<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueContainer;

// Data type casting.

$enhanced_property = new EnhancedProperty(
    'prop1',
    'string',
    new IntegerDataTypeValueContainer(100)
);

Demo\assert_true(
    $enhanced_property->getValue() === "100",
    "Incorrect value"
);
