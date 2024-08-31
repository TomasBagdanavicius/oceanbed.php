<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\Properties\Relations\AliasRelation;

$enhanced_property_1 = new EnhancedProperty('title', 'string');
$enhanced_property_2 = new EnhancedProperty('name', 'string');

$alias_relation = new AliasRelation($enhanced_property_1, $enhanced_property_2);

$enhanced_property_2->setValue("Hello World!");

echo "Inherited alias value: ";
var_dump($enhanced_property_1->getValue());
