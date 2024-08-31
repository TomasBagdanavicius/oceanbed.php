<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\DateTimeFormattingRule;
use LWP\Components\Rules\DateTimeFormatter;
use LWP\Components\DataTypes\Custom\DateTime\DateTimeDataTypeValueContainer;

/* Formatting Rule */

$date_time_formatting_rule = new DateTimeFormattingRule([
    'format' => 'Y-m-d',
    #'format' => 'l, d-M-y H:i:s {GMT}',
]);

/* Formatter */

$date_time_formatter = new DateTimeFormatter($date_time_formatting_rule);
var_dump($date_time_formatter->format('12/31/2021 23:59'));

/* Using Value Container */

$value_container = new DateTimeDataTypeValueContainer("12/31/2021 23:59");
var_dump($date_time_formatter->format($value_container));
