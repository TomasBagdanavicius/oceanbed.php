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

// Correct set value access control.

$enhanced_property = new EnhancedProperty(
    'title',
    'string',
    set_access: AccessLevelsEnum::PRIVATE
);

$enhanced_property_model = new EnhancedPropertyModel();
$enhanced_property_model->addProperty($enhanced_property);

$enhanced_property_model->occupySetAccessControlStack();
$enhanced_property_model->title = "Hello World!";
$enhanced_property_model->deoccupySetAccessControlStack();

Demo\assert_true(
    $enhanced_property_model->title === "Hello World!",
    "Property's title hasn't been correctly set"
);
