<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(__DIR__ . '/../../../../shared/definition-array.php');

use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Properties\{
    RelationalProperty,
    Exceptions\PropertyStateException
};

// Invalid state due to dependency error.

$relational_model = new RelationalPropertyModel();
$relational_model->setErrorHandlingMode(
    $relational_model::THROW_ERROR_IMMEDIATELY
);

$relational_property = new RelationalProperty(
    $relational_model,
    'prop_1',
    'string',
    dependencies: ['prop_2']
);

$expected_thrown = false;

try {
    $relational_model->prop_1 = "Hello World!";
} catch (PropertyStateException $exception) {
    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Property must have been put into invalid state due to dependency"
);
