<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\Time\ToTimeDataTypeConverter;

$converter = new ToTimeDataTypeConverter();

/* Create From */

// Timestamp
$date_value = $converter::convert(1641024000); // 2022-01-01 08:00:00
echo "From timestamp: ";
var_dump($date_value->getParser()->getHours());
var_dump($date_value->getParser()->getMinutes());
var_dump($date_value->getParser()->getSeconds());
var_dump($date_value->getParser()->getFormatted());
