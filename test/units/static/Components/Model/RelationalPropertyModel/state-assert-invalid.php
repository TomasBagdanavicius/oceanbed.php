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
        'prop_3',
    ]
);

$expected_thrown = false;

try {

    $relational_model->assertState();

} catch (InvalidStateException $exception) {

    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Model must be in invalid state due to unsolved dependencies"
);
