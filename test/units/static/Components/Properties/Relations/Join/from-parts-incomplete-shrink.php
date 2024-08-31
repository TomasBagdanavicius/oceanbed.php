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
use LWP\Components\Properties\Exceptions\PropertyValueNotAvailableException;

$full_name = new EnhancedProperty('full_name', 'string');
$first_name = new EnhancedProperty('first_name', 'string');
$middle_name = new EnhancedProperty('middle_name', 'string');
$last_name = new EnhancedProperty('last_name', 'string');

$related_properties = new EnhancedPropertyCollection();
$related_properties->add($first_name);
$related_properties->add($last_name);

$join_relation = new JoinRelation($full_name, $related_properties, [
    'separator' => ' ', // single space
    'shrink' => true
]);

$first_name->setValue("John");
$last_name->setValue("Doe");

try {
    // Middle name is missing and this is accepted due to shrinking
    $full_name_str = $full_name->getValue();
    $result = true;
} catch (PropertyValueNotAvailableException $exception) {
    $result = false;
}

Demo\assert_true($result, "Unexpected result");
