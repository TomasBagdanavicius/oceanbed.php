<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\Date\DateDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeError;

$value_container = new DateDataTypeValueContainer(2022, 1, 1);

var_dump($value_container->__toString());
var_dump($value_container->getValue());

/* Obtain Parser */

$parser = $value_container->getParser();
var_dump($parser::class);
var_dump($parser->getYear());
var_dump($parser->getMonth());
var_dump($parser->getDay());

/* Invalid Value */

try {
    $value_container = new DateDataTypeValueContainer(2022, 13, 1);
} catch (DataTypeError $exception) {
    prl("Expected error: " . $exception->getMessage());
}
