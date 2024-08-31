<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\{
    RelationalProperty,
    Exceptions\PropertyStateException
};
use LWP\Components\Model\RelationalPropertyModel;

// Invalid state from bad dependency.

$relational_model = new RelationalPropertyModel();

$relational_property = new RelationalProperty(
    $relational_model,
    'prop_1',
    'string',
    dependencies: ['prop_2']
);

if ($relational_property->isInValidState()) {
    throw new \Error("Property should not be in valid state");
}

$expected_thrown = false;

try {
    $relational_property->setValue("Hello World!");
} catch (PropertyStateException $exception) {
    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Expected PropertyStateException not thrown"
);
