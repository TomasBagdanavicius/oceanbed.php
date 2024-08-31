<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\Properties\Relations\MismatchRelation;
use LWP\Components\Properties\Exceptions\PropertyValueContainsErrorsException;

$enhanced_property_1 = new EnhancedProperty('title', 'string');
$enhanced_property_2 = new EnhancedProperty('name', 'string');
$enhanced_property_3 = new EnhancedProperty('subname', 'string');

$match_relation = new MismatchRelation($enhanced_property_1, $enhanced_property_2);

try {

    $enhanced_property_1->setValue("Hello World!");
    $enhanced_property_2->setValue("Hello World!");

} catch (PropertyValueContainsErrorsException $exception) {

    prl("Expected error: " . $exception->getMessage() . " " . $exception->getPrevious()->getMessage());
}

// No violation.
$enhanced_property_2->setValue("Hey World!");
