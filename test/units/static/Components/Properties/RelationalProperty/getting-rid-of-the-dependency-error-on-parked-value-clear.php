<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\RelationalProperty;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Constraints\MaxSizeConstraint;

// Getting rid of the dependency error on parked value clear.

$relational_model = new RelationalPropertyModel();
$relational_model->setErrorHandlingMode($relational_model::COLLECT_ERRORS);

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

$max_size_constraint = new MaxSizeConstraint(10);
$relational_property_2->setConstraint($max_size_constraint);

// Dependency is set and.
$relational_model->prop_2 = "Property 2 value";
// Parked value will be cleared.
$relational_model->prop_1 = "Property 1 value";

$error_count = $relational_property_2->getErrorsAsViolationCollection()
    ->count();

Demo\assert_true(
    // Dependency error should have been removed, hence just one max size error.
    $error_count === 1,
    "Error count is not equal to 1"
);
