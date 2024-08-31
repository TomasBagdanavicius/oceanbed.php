<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\DateTimeFormat;
use LWP\Components\Rules\DateTimeFormattingRule;
use LWP\Components\Rules\Exceptions\DateTimeFormatNegotiationException;
use LWP\Database\DateTimeFormatMap as SqlDateTimeFormatMap;

$date_time_formatting_rule = new DateTimeFormattingRule([
    // "L" and "o" are not available in the SQL date time format map.
    'format' => 'Ym Y L o H:i:s',
]);
$date_time_format = new DateTimeFormat($date_time_formatting_rule, new SqlDateTimeFormatMap());

try {
    $format = $date_time_format->getFormat();
    $result = false;
} catch (DateTimeFormatNegotiationException) {
    $result = true;
}

Demo\assert_true($result, "Expected error not thrown");
