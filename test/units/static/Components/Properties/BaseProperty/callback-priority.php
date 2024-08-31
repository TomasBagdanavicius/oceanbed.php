<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\BaseProperty;

// Callback priority.

$property = new BaseProperty('title', 'string');

$result_string = '';

$property->onBeforeSetValue(
    function (mixed $property_value) use (&$result_string): mixed {
        $result_string .= '1';
        return $property_value;
    },
    priority: 2
);

$property->onBeforeSetValue(
    function (mixed $property_value) use (&$result_string): mixed {
        $result_string .= '2';
        return $property_value;
    },
    priority: 3
);

$property->onBeforeSetValue(
    function (mixed $property_value) use (&$result_string): mixed {
        $result_string .= '3';
        return $property_value;
    },
    priority: 1
);

$property->setValue("Hello World!");

Demo\assert_true(
    $result_string === '312',
    "Incorrect result string"
);
