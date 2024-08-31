<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Definitions\DefaultDefinition;

$default_definition = new DefaultDefinition("Default value");

var_dump($default_definition->getIndexableData());
var_dump($default_definition->getArray());

$default_definition->setValue("New default value");
var_dump($default_definition->getValue());

/* Produce Class Object */

var_dump($default_definition->canProduceClassObject());
