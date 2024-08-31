<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\DateTimeFormattingRule;

$date_time_formatting_rule = new DateTimeFormattingRule([
    'format' => 'D, j M Y H:i:s O',
]);

$date_time_formatting_rule2 = new DateTimeFormattingRule([
    // Mismatching format.
    'format' => 'l, d-M-Y H:i:s T',
]);

Demo\assert_true(
    $date_time_formatting_rule->matches($date_time_formatting_rule2) === false,
    "Unexpected result"
);
