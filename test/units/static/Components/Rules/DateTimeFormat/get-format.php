<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\DateTimeFormat;
use LWP\Components\Rules\DateTimeFormattingRule;
use LWP\Database\DateTimeFormatMap as SqlDateTimeFormatMap;

$date_time_formatting_rule = new DateTimeFormattingRule([
    // eg. 8:00 AM on Sunday 1st January 2023
    // `date('g:i A \o\n l jS F Y', 1672560000)`
    'format' => 'g:i A {on} l jS F Y',
]);
$date_time_format = new DateTimeFormat($date_time_formatting_rule, new SqlDateTimeFormatMap());

Demo\assert_true(
    $date_time_format->getFormat() === "%l:%i %p on %W %D %M %Y",
    "Unexpected result"
);
