<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\{
    EnhancedProperty,
    Exceptions\PropertyValueContainsErrorsException
};
use LWP\Components\Violations\GenericViolation;

// Violation injection.

$enhanced_property = new EnhancedProperty('title', 'string');

$enhanced_property->onBeforeSetValue(
    function (mixed $property_value): mixed {

        if ($property_value == "Hello World!") {

            $violation = new GenericViolation();
            $violation->setErrorMessageString("There was an error!");

            return $violation;
        }

        return $property_value;
    }
);

try {
    $enhanced_property->setValue("Hello World!");
} catch (PropertyValueContainsErrorsException $exception) {

}

Demo\assert_true(
    (
        isset($exception)
        && ($enhanced_property->hasErrors() === true)
    ),
    "Property does not contain injected error"
);
