<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\RelationalProperty;
use LWP\Components\Model\RelationalPropertyModel;

// Unassigned dependencies causing invalid state.

$relational_model = new RelationalPropertyModel();

$relational_property_1 = new RelationalProperty(
    $relational_model,
    'prop_1',
    'string',
    dependencies: [
        'prop_2'
    ]
);

// Can't be valid.
if ($relational_property_1->isInValidState()) {
    throw new \Error("Initially property should not be in valid state");
}

$relational_property_2 = new RelationalProperty(
    $relational_model,
    'prop_2',
    'string'
);

Demo\assert_true(
    // Now it should be valid.
    $relational_property_1->isInValidState(),
    "Expected property to be in valid state"
);
