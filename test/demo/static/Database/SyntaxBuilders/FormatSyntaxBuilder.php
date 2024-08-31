<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Database\SyntaxBuilders\FormatSyntaxBuilder;
use LWP\Components\Rules\NumberFormattingRule;

$number = '123 456.78';

/* Formatting Rule */

$number_formatting_rule = new NumberFormattingRule([
    'fractional_part_length' => 3,
    /* All other options cannot be changed. If the below is used, it will throw "UnsupportedFormattingRuleConfigException".
    'integer_part_group_length' => 4, */
]);

/* Syntax Builder */

$format_syntax_builder = new FormatSyntaxBuilder($number_formatting_rule);

prl($format_syntax_builder->getFunctionSyntax($number, FormatSyntaxBuilder::VALUE_NUMBER));
prl($format_syntax_builder->getFunctionSyntax('col_name', FormatSyntaxBuilder::VALUE_COLUMN));
