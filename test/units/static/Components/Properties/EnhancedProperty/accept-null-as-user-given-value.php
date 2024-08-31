<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;

// Accept null as user given value.

$enhanced_property = new EnhancedProperty(
    'prop_1',
    'string',
    required: true,
    nullable: true
);

$enhanced_property->setValue(null);

Demo\assert_true(
    $enhanced_property->getValue() === null,
    "Value is not equal to NULL"
);
