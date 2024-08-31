<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\BaseProperty;
use LWP\Components\Properties\Enums\HookNamesEnum;

// Throttled hooks.

$property = new BaseProperty('title', 'string');

$result_string = '';

$property->throttleHooks(HookNamesEnum::BEFORE_SET_VALUE);

$property->onBeforeSetValue(
    function (mixed $property_value) use (&$result_string): mixed {
        $result_string .= '1';
        return $property_value;
    }
);

$property->onBeforeSetValue(
    function (mixed $property_value) use (&$result_string): mixed {
        $result_string .= '2';
        return $property_value;
    }
);

$property->setValue("Hello World!");

Demo\assert_true(
    $result_string === '',
    "Incorrect hook string"
);
