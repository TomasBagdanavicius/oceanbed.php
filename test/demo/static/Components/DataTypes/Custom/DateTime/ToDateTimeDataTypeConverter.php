<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\DateTime\ToDateTimeDataTypeConverter;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;

$converter = new ToDateTimeDataTypeConverter();

/* Create From */

// Timestamp
$datetime_value = $converter::convert(1641024000); // 2022-01-01 08:00:00
echo "From timestamp: ";
var_dump($datetime_value->getParser()->format('Y-m-d H:i:s'));

// Date-time string
$datetime_value = $converter::convert('2022-01-01 08:00:00');
echo "From date-time string";
var_dump($datetime_value->getParser()->getTimestamp());

// DateTime object
$datetime_value = $converter::convert(new \DateTime());
echo "From DateTime object: ";
var_dump($datetime_value->getParser()->format('Y-m-d H:i:s'));

// "StringDataTypeValueContainer" object
$string_data_type_value = new StringDataTypeValueContainer('2022-01-01 08:00:00');
$datetime_value = $converter::convert($string_data_type_value);
echo "From StringDataTypeValueContainer: ";
var_dump($datetime_value->getParser()->getTimestamp());

echo "Value container class name: ";
var_dump($converter::getValueContainerClassName());
