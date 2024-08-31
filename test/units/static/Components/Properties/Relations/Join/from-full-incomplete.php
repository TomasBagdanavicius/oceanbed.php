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
]);

try {
    // Middle name is missing
    $full_name->setValue("John Doe");
    $result = false;
} catch (\LengthException $exception) {
    $result = true;
}

Demo\assert_true($result, "Unexpected result");
