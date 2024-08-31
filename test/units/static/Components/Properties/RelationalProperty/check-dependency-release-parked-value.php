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

// Check dependency, release parked value.

$relational_model = new RelationalPropertyModel();

$relational_property_1 = new RelationalProperty(
    $relational_model,
    'prop_1',
    'string'
);

$relational_property_2 = new RelationalProperty(
    $relational_model,
    'prop_2',
    'string',
    dependencies: [
        'prop_1',
    ]
);

try {
    $relational_property_2->setValue("Hello World!");
} catch (PropertyDependencyException $exception) {
    // Continue.
}

$relational_property_1->setValue("Hey!");

Demo\assert_true(
    $relational_property_2->getValue() === "Hello World!",
    "Incorrect value"
);
