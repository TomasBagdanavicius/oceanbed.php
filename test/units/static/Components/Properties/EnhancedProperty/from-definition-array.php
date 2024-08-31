<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;

// From definition array.

$enhanced_property = EnhancedProperty::fromDefinitionArray('title', [
    'type' => 'string',
    'default' => "This is my title from a definition array",
    'description' => "My title.",
]);

$enhanced_property->setValue("This is my very long custom title.");

$title = "This is my very long custom title.";

Demo\assert_true(
    $enhanced_property->getValue() === $title,
    "Incorrect value"
);
