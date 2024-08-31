<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\RelationalProperty;
use LWP\Components\Model\RelationalPropertyModel;

// Change required state.

$relational_model = new RelationalPropertyModel();

$relational_property = new RelationalProperty(
    $relational_model,
    'prop_1',
    'string',
    relational_required: false
);

Demo\assert_true(
    $relational_property->isRequired() === false,
    "Property should not be required"
);
