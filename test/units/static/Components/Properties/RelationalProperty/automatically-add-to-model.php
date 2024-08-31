<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\RelationalProperty;
use LWP\Components\Model\RelationalPropertyModel;

// Automatically add to model.

$relational_model = new RelationalPropertyModel();

$relational_property = new RelationalProperty(
    $relational_model,
    'prop_1',
    'string'
);

Demo\assert_true(
    $relational_model->propertyExists('prop_1') === true,
    "Property was not found in model"
);
