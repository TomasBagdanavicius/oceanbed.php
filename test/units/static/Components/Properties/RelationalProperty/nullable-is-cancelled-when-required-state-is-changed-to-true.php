<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\{
    RelationalProperty,
    Exceptions\PropertyValueNotAvailableException
};
use LWP\Components\Model\RelationalPropertyModel;

// Nullable is cancelled when required state is changed to "true".

$relational_model = new RelationalPropertyModel();

$relational_property = new RelationalProperty(
    $relational_model,
    'prop_1',
    'string',
    relational_required: false,
    nullable: true
);

if ($relational_property->getValue() !== null) {
    throw new \Error("Initial value should be null");
}

$relational_property->setRequired(true);

$expected_thrown = false;

try {
    $value = $relational_property->getValue();
} catch (PropertyValueNotAvailableException $exception) {
    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Expected PropertyValueNotAvailableException not thrown"
);
