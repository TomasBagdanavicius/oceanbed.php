<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Definitions\MaxDefinition;

$max_definition = new MaxDefinition(100);

var_dump($max_definition->getIndexableData());
var_dump($max_definition->getArray());

$max_definition->setValue(50);
var_dump($max_definition->getValue());

/* Produce Class Object */

var_dump($max_definition->canProduceClassObject());
var_dump($max_definition::getClassObjectClassName());
var_dump($max_definition->produceClassObject());
