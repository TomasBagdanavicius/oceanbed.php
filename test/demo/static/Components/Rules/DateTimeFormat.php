<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\DateTimeFormat;
use LWP\Components\Rules\DateTimeFormattingRule;
use LWP\Database\DateTimeFormatMap as SqlDateTimeFormatMap;
use LWP\Components\Rules\Exceptions\DateTimeFormatNegotiationException;

$date_time_formatting_rule = new DateTimeFormattingRule([
    'format' => 'g:i a {on} l jS F Y',
]);

$date_time_format = new DateTimeFormat($date_time_formatting_rule, new SqlDateTimeFormatMap());

echo "To SQL format: ";
var_dump($date_time_format->getFormat());

/* Error Simulation: Format Conversion Error */

$date_time_formatting_rule = new DateTimeFormattingRule([
    // "L" and "o" are not available in the SQL date time format map.
    'format' => 'Ym Y L o H:i:s',
]);

$date_time_format = new DateTimeFormat($date_time_formatting_rule, new SqlDateTimeFormatMap());

try {
    var_dump($date_time_format->getFormat());
} catch (DateTimeFormatNegotiationException $exception) {
    prl("Expected error: " . $exception->getMessage());
}

/* Static Parsing */

print_r(DateTimeFormat::parseFormat('Y-m-d {T}H:i:s {custom text %here%}', return_as_array: true));

/* Custom Format to Standard Format */

var_dump(DateTimeFormat::customFormatToStandardFormat('Y-m-d {T}H:i:s {GMT}'));

/* Date Time Testing Helper */

var_dump(date('l, d-M-y H:i:s o', time()));
