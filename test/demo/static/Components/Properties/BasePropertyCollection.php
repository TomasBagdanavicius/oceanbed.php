<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\BaseProperty;
use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\Attributes\NoDefaultValueAttribute;
use LWP\Components\Properties\BasePropertyCollection;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;

/* Property */

$property1 = new BaseProperty('title', 'string', new NoDefaultValueAttribute());
// Incorrect property class.
#$property1 = new EnhancedProperty('title', 'string', new NoDefaultValueAttribute);
$property2 = new BaseProperty('name', 'string', new StringDataTypeValueContainer('default'));

/* Collection */

$base_property_collection = new BasePropertyCollection();

$base_property_collection->add($property1);
$base_property_collection->add($property2);

print "Count: ";
var_dump($base_property_collection->count());
