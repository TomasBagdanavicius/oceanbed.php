<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;
use LWP\Common\Exceptions\ApplicationAbortException;

// Handle application error.

try {

    $enhanced_property = new EnhancedProperty(
        'prop1',
        'string',
        function (EnhancedProperty $property) {
            throw new \Error("Application error");
        }
    );

} catch (ApplicationAbortException $exception) {

}

Demo\assert_true(
    (
        isset($exception)
        // Application exception was recorded into previous.
        && ($previous = $exception->getPrevious())
        // Verify the instance of the exception that was thrown above.
        && ($previous instanceof \Error)
        && ($previous->getMessage() === "Application error")
    ),
    "Application error not handled correctly"
);
