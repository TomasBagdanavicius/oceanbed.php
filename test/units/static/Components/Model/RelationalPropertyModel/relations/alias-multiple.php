<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Properties\RelationalProperty;

// Alias multiple.

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
$relational_property_3 = new RelationalProperty(
    $relational_model,
    'prop_3',
    'string'
);

// Prop 2 is an alias of prop 1.
$relational_property_2->setupRelation('alias', 'prop_1');
// Prop 3 is an alias of prop 2.
$relational_property_3->setupRelation('alias', 'prop_2');

$relational_model->prop_1 = "Value 1";

Demo\assert_true(
    (
        $relational_model->prop_1 === "Value 1"
        && $relational_model->prop_2 === "Value 1"
        && $relational_model->prop_3 === "Value 1"
    ),
    "Incorrect property values"
);
