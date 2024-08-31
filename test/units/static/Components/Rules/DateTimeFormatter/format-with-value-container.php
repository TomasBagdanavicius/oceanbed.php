<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\DateTimeFormattingRule;
use LWP\Components\Rules\DateTimeFormatter;
use LWP\Components\DataTypes\Custom\DateTime\DateTimeDataTypeValueContainer;

/* Formatting Rule */

$date_time_formatting_rule = new DateTimeFormattingRule([
    'format' => 'l, d-M-y H:i:s {GMT}',
]);
$date_time_formatter = new DateTimeFormatter($date_time_formatting_rule);
$value_container = new DateTimeDataTypeValueContainer("12/31/2021 23:59");
$format = $date_time_formatter->format($value_container);

Demo\assert_true(
    $format === "Friday, 31-Dec-21 23:59:00 GMT",
    "Unexpected result"
);
