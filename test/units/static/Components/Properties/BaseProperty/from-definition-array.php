<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\BaseProperty;

// From definition array.

$definition_array = [
    'type' => 'string',
    'default' => "Hello World!",
    'description' => "Main Title.",
];

$base_property = BaseProperty::fromDefinitionArray(
    'title',
    $definition_array
);

Demo\assert_true(
    $base_property->getValue() === "Hello World!",
    "Incorrect value"
);
