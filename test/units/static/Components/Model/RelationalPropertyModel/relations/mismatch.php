<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Properties\RelationalProperty;
use LWP\Components\Properties\Exceptions\PropertyValueContainsErrorsException;

// Mismatch.

$relational_model = new RelationalPropertyModel();
$relational_model->setErrorHandlingMode(
    $relational_model::THROW_ERROR_IMMEDIATELY
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

// Prop 2 should NOT match prop 1.
$relational_property_2->setupRelation('mismatch', 'prop_1');

$expected_thrown = false;

try {

    // Setting matching values.
    $relational_model->prop_1 = "Value 1";
    $relational_model->prop_2 = "Value 1";

} catch (PropertyValueContainsErrorsException $exception) {

    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Error not thrown for values that should not match"
);
