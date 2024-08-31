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

// Dependency and hook call priority.

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

$was_on_before_set_value_called = false;

$relational_property_2->onBeforeSetValue(
    function (
        mixed $property_value
    ) use (
        &$was_on_before_set_value_called
    ): mixed {
        $was_on_before_set_value_called = true;
        return $property_value;
    }
);

try {

    $relational_property_2->setValue("Hello World!");

} catch (PropertyDependencyException $exception) {

    // Continue.
}

$relational_property_1->setValue("Hey!");

if (!$was_on_before_set_value_called) {
    throw new \Error("Hook should have been called");
}

Demo\assert_true(
    (
        $relational_property_1->getValue() === "Hey!"
        && $relational_property_2->getValue() === "Hello World!"
    ),
    "Incorrect property values"
);
