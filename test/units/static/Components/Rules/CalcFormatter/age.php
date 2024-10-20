<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\CalcFormattingRule;

$years_ago = '25';
$options = [
    'subject' => 'age',
];
$formatting_rule = new CalcFormattingRule($options);
$formatter = $formatting_rule->getFormatter();

$date = new DateTime();
$date->modify("-$years_ago years");
$past_date_formatted = $date->format('Y-m-d');

Demo\assert_true(
    $formatter->format($past_date_formatted) === $years_ago,
    "Unexpected result"
);
