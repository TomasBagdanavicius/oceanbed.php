<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\Attributes\NoDefaultValueAttribute;
use LWP\Components\Properties\EnhancedPropertyCollection;

$property1 = new EnhancedProperty('title', 'string', new NoDefaultValueAttribute());
$property2 = new EnhancedProperty('name', 'string', new NoDefaultValueAttribute());

/* Property Collection */

$enhanced_property_collection = new EnhancedPropertyCollection();

$enhanced_property_collection->add($property1);
$enhanced_property_collection->add($property2);

var_dump($enhanced_property_collection->count());
