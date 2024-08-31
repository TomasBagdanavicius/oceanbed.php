<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\Properties\{
    Exceptions\PropertyValueNotAvailableException,
    Exceptions\PropertyValueNotSetException
};

// Unsetting destroys default value.

$enhanced_property = new EnhancedProperty(
    'prop_1',
    'string',
    new StringDataTypeValueContainer("Hello World!")
);

$enhanced_property->unsetValue();

try {
    $enhanced_property->getValue();
} catch (PropertyValueNotAvailableException $exception) {

}

Demo\assert_true(
    (
        isset($exception)
        && ($previous = $exception->getPrevious())
        && ($previous instanceof PropertyValueNotSetException)
    ),
    "Incorrect value handling"
);
