<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\TimeDuration\TimeDurationDataTypeValueContainer;

$value_container = new TimeDurationDataTypeValueContainer('P1YT4H5M');
prl("Value: " . $value_container->getValue());

/* Obtain Parser */

$parser = $value_container->getParser();
echo "Parser class: ";
var_dump($parser::class);

echo "Years: ";
var_dump($parser->getYears());

echo "Minutes: ";
var_dump($parser->getMinutes());
