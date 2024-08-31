<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\Properties\Relations\JoinRelation;
use LWP\Components\Properties\EnhancedPropertyCollection;

$full_name = new EnhancedProperty('full_name', 'string');
$first_name = new EnhancedProperty('first_name', 'string');
$middle_name = new EnhancedProperty('middle_name', 'string');
$last_name = new EnhancedProperty('last_name', 'string');

$related_properties = new EnhancedPropertyCollection();
$related_properties->add($first_name);
$related_properties->add($middle_name);
$related_properties->add($last_name);

$join_relation = new JoinRelation($full_name, $related_properties, [
    'separator' => ' ', // single space
    'shrink' => true,
    'shrink_order' => [
        2 => [
            'first_name',
            'last_name',
        ]
    ]
]);

try {
    // Middle name is missing, but accepted due to shrinking
    $full_name->setValue("John Doe");
} catch (\LengthException $exception) {
    throw new \RuntimeException("Length exception was incorrectly thrown when shrinking is enabled");
}

Demo\assert_true(
    $first_name->getValue() === "John"
    && $last_name->getValue() === "Doe",
    "Unexpected result"
);
