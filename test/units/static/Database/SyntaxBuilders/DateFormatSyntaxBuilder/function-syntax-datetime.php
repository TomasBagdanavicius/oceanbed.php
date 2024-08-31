<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Database\SyntaxBuilders\DateFormatSyntaxBuilder;
use LWP\Components\Rules\DateTimeFormattingRule;
use LWP\Database\SyntaxBuilders\Enums\FormatSyntaxBuilderValueEnum;

$date_time_string = '2023-01-02 08:30:45';
$date_time_formatting_rule = new DateTimeFormattingRule([
    'format' => 'F jS, Y H:i:s',
]);
$date_format_syntax_builder = new DateFormatSyntaxBuilder($date_time_formatting_rule);
$syntax = $date_format_syntax_builder->getFunctionSyntax($date_time_string, FormatSyntaxBuilderValueEnum::VALUE_TYPE);

Demo\assert_true(
    $syntax === "DATE_FORMAT('2023-01-02 08:30:45', '%M %D, %Y %T')",
    "Unexpected result"
);
