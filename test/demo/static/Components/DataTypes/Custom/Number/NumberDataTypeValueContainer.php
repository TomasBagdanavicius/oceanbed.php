<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\Number\NumberDataTypeValueContainer;

$value_container = new NumberDataTypeValueContainer("12 345 678.90");

/* Obtain Parser */

$parser = $value_container->getParser();
var_dump($parser::class);
var_dump($parser->getInteger());
