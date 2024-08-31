<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\{
    BaseProperty,
    Exceptions\PropertyValueNotAvailableException,
    Exceptions\PropertyValueNotSetException
};

// No main or default value.

$base_property = new BaseProperty('prop1', 'string');

try {
    $base_property->getValue();
} catch (PropertyValueNotAvailableException $exception) {

}

Demo\assert_true(
    (
        isset($exception)
        && ($previous = $exception->getPrevious())
        && ($previous instanceof PropertyValueNotSetException)
    ),
    "Incorrect exception result"
);
