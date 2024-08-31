<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\Properties\Relations\JoinRelation;
use LWP\Components\Properties\EnhancedPropertyCollection;

$prime_property = new EnhancedProperty('full_name', 'string');
$secondary_property_1 = new EnhancedProperty('first_name', 'string');
$secondary_property_2 = new EnhancedProperty('middle_name', 'string', nullable: true);
$secondary_property_3 = new EnhancedProperty('last_name', 'string');

$enhanced_property_collection = new EnhancedPropertyCollection();
$enhanced_property_collection->add($secondary_property_1);
$enhanced_property_collection->add($secondary_property_2);
$enhanced_property_collection->add($secondary_property_3);

$join_relation = new JoinRelation($prime_property, $enhanced_property_collection, [
    'separator' => ' ', // single space
    'shrink' => true,
    'shrink_order' => [
        2 => [
            'first_name',
            'last_name'
        ]
    ]
]);

/* $secondary_property_1->setValue("John");
#$secondary_property_2->setValue(null);
$secondary_property_3->setValue("Doe");
var_dump( $prime_property->getValue() ); */

$prime_property->setValue("John Doe");
var_dump($secondary_property_1->getValue());
var_dump($secondary_property_2->getValue());
var_dump($secondary_property_3->getValue());

/* $prime_property->setValue("John Doe");
#$prime_property->unsetValue();
var_dump( $secondary_property_1->getValue() );
var_dump( $secondary_property_2->getValue() ); */
