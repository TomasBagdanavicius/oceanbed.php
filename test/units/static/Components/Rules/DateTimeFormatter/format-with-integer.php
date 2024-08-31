<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\DateTimeFormattingRule;
use LWP\Components\Rules\DateTimeFormatter;

date_default_timezone_set('GMT');

/* Formatting Rule */

$date_time_formatting_rule = new DateTimeFormattingRule([
    'format' => 'l, d-M-y H:i:s {GMT}',
]);
$date_time_formatter = new DateTimeFormatter($date_time_formatting_rule);
$format = $date_time_formatter->format(1640995140);
$expected_date_format = "Friday, 31-Dec-21 23:59:00 GMT";

Demo\assert_true(
    $format === $expected_date_format,
    "Expected $expected_date_format, got $format"
);
