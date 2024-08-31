<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Definitions\TypeDefinition;

$type_definition = new TypeDefinition("string");

var_dump($type_definition->getIndexableData());
var_dump($type_definition->getArray());

$type_definition->setValue("boolean");
var_dump($type_definition->getValue());

/* Produce Class Object */

var_dump($type_definition->getClassObjectClassName());
