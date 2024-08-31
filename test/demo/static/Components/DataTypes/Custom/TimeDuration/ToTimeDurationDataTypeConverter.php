<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\TimeDuration\ToTimeDurationDataTypeConverter;

$converter = new ToTimeDurationDataTypeConverter();

/* Create From */

// From date string.
$date_string = '1 year + 2 months + 4 hours + 5 minutes';
$time_duration_value = $converter::convert($date_string);
print "From a date string ({$date_string}): ";
var_dump($time_duration_value->getValue());

// From "DateInterval" object.
$date_string = '3 days + 5 minutes + 6 seconds';
$date_interval = \DateInterval::createFromDateString($date_string);
$time_duration_value = $converter::convert($date_interval);
echo "From a DateInterval object ({$date_string}): ";
var_dump($time_duration_value->getValue());
