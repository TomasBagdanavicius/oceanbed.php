<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\{
    RelationalProperty,
    Exceptions\PropertyDependencyException
};
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Common\Enums\AccessLevelsEnum;

// Release parked value that originally was attempted to set through a private
// channel.

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
    'string',
    dependencies: ['prop_1'],
    set_access: AccessLevelsEnum::PRIVATE
);

$relational_model->occupySetAccessControlStack();

try {

    $relational_model->prop_2 = "Property 2 value";

} catch (PropertyDependencyException $exception) {

    // Save and continue.

} finally {

    $relational_model->deoccupySetAccessControlStack();
}

if (!isset($exception)) {
    throw new \Error("Expected exception not thrown");
}

// Parked value for property 2 will be released.
$relational_model->prop_1 = "Property 1 value";

Demo\assert_true(
    (
        $relational_model->prop_1 === "Property 1 value"
        && $relational_model->prop_2 === "Property 2 value"
    ),
    "Incorrect property values"
);
