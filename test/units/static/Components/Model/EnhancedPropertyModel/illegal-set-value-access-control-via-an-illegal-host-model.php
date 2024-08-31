<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Model\EnhancedPropertyModel;
use LWP\Components\Properties\EnhancedProperty;
use LWP\Common\Enums\AccessLevelsEnum;

// Illegal set value access control via an illegal host model.

$property = new EnhancedProperty(
    'title',
    'string',
    set_access: AccessLevelsEnum::PRIVATE
);

$enhanced_property_model = new EnhancedPropertyModel();
$enhanced_property_model_2 = new EnhancedPropertyModel();

$enhanced_property_model->addProperty($property);
$enhanced_property_model_2->occupySetAccessControlStack();

try {

    $property->setValuePrivate($enhanced_property_model_2, "Hello World!");

    // Illegal attempt.
} catch (\RuntimeException $exception) {

    // Continue.
}

$enhanced_property_model_2->deoccupySetAccessControlStack();

Demo\assert_true(
    $property->hasValue() === false,
    "Property's value hasn't been correctly set"
);
