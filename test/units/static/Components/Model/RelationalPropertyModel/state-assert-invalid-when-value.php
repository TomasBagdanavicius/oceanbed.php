<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(__DIR__ . '/../../../../shared/definition-array.php');

use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Properties\RelationalProperty;
use LWP\Components\Properties\Exceptions\PropertyDependencyException;

// State and hooks order when dependency.

$relational_model = new RelationalPropertyModel();

$relational_model->setErrorHandlingMode(
    $relational_model::THROW_ERROR_IMMEDIATELY
);

$relational_property = new RelationalProperty(
    $relational_model,
    'prop_1',
    'string',
    dependencies: [
        'prop_2',
    ]
);

// Registering property 2 will upgrade from InvalidStateException to
// PropertyDependencyException error.
$relational_property_2 = new RelationalProperty(
    $relational_model,
    'prop_2',
    'string'
);

$expected_thrown = false;

try {

    $relational_model->prop_1 = "Hello World!";

} catch (PropertyDependencyException $exception) {

    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "No error thrown when setting value of property with unsolved dependencies"
);
