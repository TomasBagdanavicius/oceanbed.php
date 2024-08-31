<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\{
    RelationalProperty,
    Exceptions\PropertyDependencyException
};
use LWP\Components\Model\RelationalPropertyModel;

// Dependency error.

$relational_model = new RelationalPropertyModel();

$relational_property = new RelationalProperty(
    $relational_model,
    'prop_1',
    'string',
    dependencies: ['prop_2']
);

$relational_property_2 = new RelationalProperty(
    $relational_model,
    'prop_2',
    'string'
);

$expected_thrown = false;

try {
    $relational_property->setValue("Hello World!");
} catch (PropertyDependencyException $exception) {
    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Expected PropertyDependencyException not thrown"
);
