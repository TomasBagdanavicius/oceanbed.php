<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\Time\TimeDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeError;

$value_container = new TimeDataTypeValueContainer(8, 12, 30);

var_dump($value_container->__toString());
var_dump($value_container->getValue());

/* Obtain Parser */

$parser = $value_container->getParser();
var_dump($parser::class);
var_dump($parser->getHours());
var_dump($parser->getMinutes());
var_dump($parser->getSeconds());

/* Invalid Value */

try {
    $time_value = new TimeDataTypeValueContainer(25, 30, 45);
} catch (DataTypeError $exception) {
    prl("Expected error: " . $exception->getMessage());
}

/* Create From */

/* $time_value = TimeDataTypeValue::from('2022-01-01 15:30:45');
var_dump( $time_value->getValue() );

$time_value = TimeDataTypeValue::from(1641024000); // 2022-01-01 08:00:00
var_dump( $time_value->getValue() ); */
