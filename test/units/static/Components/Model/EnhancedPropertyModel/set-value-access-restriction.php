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
use LWP\Components\Properties\Exceptions\PropertySetAccessRestrictedException;

// Set value access restriction.

$property = new EnhancedProperty(
    'title',
    'string',
    set_access: AccessLevelsEnum::PRIVATE
);

$enhanced_property_model = new EnhancedPropertyModel();
$enhanced_property_model->addProperty($property);

$error_thrown = false;

try {

    $enhanced_property_model->title = "Hello World";

} catch (PropertySetAccessRestrictedException $exception) {

    $error_thrown = true;
}

Demo\assert_true(
    $error_thrown,
    "Access to value of a protected property was not restricted"
);
