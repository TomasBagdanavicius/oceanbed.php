<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\DateTime\DateTimeDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeError;

$value_container = new DateTimeDataTypeValueContainer("2022-01-01 08:00:00");

echo "Value: ";
var_dump($value_container->getValue());
echo "To string: ";
var_dump($value_container->__toString());
echo "Size: ";
var_dump($value_container->getSize());

/* Obtain Parser */

$parser = $value_container->getParser();
var_dump($parser::class);
var_dump($parser->getTimestamp());

/* Invalid Value */

try {
    $value_container = new DateTimeDataTypeValueContainer('2022-13-01 08:00:00');
} catch (DataTypeError $exception) {
    prl("Expected error: " . $exception->getMessage());
}
