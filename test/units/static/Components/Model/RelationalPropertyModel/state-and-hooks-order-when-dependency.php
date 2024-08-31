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
use LWP\Common\Exceptions\InvalidStateException;
use LWP\Components\Properties\Exceptions\PropertyDependencyException;

// State and hooks order when dependency.

$relational_model = new RelationalPropertyModel();

$relational_model->setErrorHandlingMode(
    $relational_model::THROW_ERROR_IMMEDIATELY
);

$relational_property_3 = new RelationalProperty(
    $relational_model,
    'prop_3',
    'string',
    dependencies: [
        'prop_1',
        'prop_2',
    ]
);

$relational_property_1 = new RelationalProperty(
    $relational_model,
    'prop_1',
    'string'
);
$relational_property_2 = new RelationalProperty(
    $relational_model,
    'prop_2',
    'string'
);

$result_string = '';

$relational_model->onBeforeSetValue(
    function (
        mixed $property_value,
        RelationalPropertyModel $model,
        string $property_name
    ) use (
        &$result_string,
    ): mixed {

        if ($property_name === 'prop_3') {
            $result_string .= 'b';
        }

        return $property_value;

    }
);

$relational_model->onAfterSetValue(
    function (
        mixed $property_value,
        RelationalPropertyModel $model,
        string $property_name
    ) use (
        &$result_string,
    ): mixed {

        if ($property_name === 'prop_3') {
            $result_string .= 'a';
        }

        return $property_value;

    }
);

// This should fail due to dependencies, but it will also part the value.
try {
    $relational_model->prop_3 = "Hello World!";
} catch (PropertyDependencyException) {
    // Continue.
}

if ($result_string !== '') {
    throw new Error("Hook string must be empty for now");
}

$relational_model->prop_1 = "Property 1 value";
$relational_model->prop_2 = "Property 2 value";

Demo\assert_true(
    (
        $result_string === 'ba'
        && $relational_model->prop_3 === "Hello World!"
    ),
    "Incorrect results"
);
