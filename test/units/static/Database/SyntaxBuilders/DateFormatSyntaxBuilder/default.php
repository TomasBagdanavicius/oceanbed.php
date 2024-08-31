<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Database\SyntaxBuilders\DateFormatSyntaxBuilder;
use LWP\Components\Rules\DateTimeFormattingRule;

$date_time_string = '2022-01-02 08:30:45';
$date_time_formatting_rule = new DateTimeFormattingRule([
    'format' => 'F jS, Y H:i:s',
]);
$date_format_syntax_builder = new DateFormatSyntaxBuilder($date_time_formatting_rule);

Demo\assert_true(
    $date_format_syntax_builder instanceof DateFormatSyntaxBuilder,
    "Unexpected result"
);
