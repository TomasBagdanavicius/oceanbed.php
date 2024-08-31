<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\Constraints\MaxSizeConstraint;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueContainer;

// Revalidate default value on new constraint registration.

$enhanced_property = new EnhancedProperty(
    'prop1',
    'integer',
    new IntegerDataTypeValueContainer(100)
);

$expected_thrown = false;

try {
    $enhanced_property->setConstraint(new MaxSizeConstraint(50));
} catch (\RuntimeException $exception) {
    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Expected exception not thrown"
);
