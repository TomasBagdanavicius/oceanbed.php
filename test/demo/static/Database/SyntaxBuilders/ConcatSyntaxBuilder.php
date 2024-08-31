<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Database\SyntaxBuilders\ConcatSyntaxBuilder;
use LWP\Components\Rules\ConcatFormattingRule;
use LWP\Database\SyntaxBuilders\Enums\FormatSyntaxBuilderValueEnum;

$multi_value = [
    "foo",
    "bar",
    "baz"
];

/* Formatting Rule */

$concat_formatting_rule = new ConcatFormattingRule([
    'separator' => ' ',
]);

/* Syntax Builder */

$concat_syntax_builder = new ConcatSyntaxBuilder($concat_formatting_rule);

prl($concat_syntax_builder->getFunctionSyntax($multi_value, FormatSyntaxBuilderValueEnum::VALUE_TYPE));
prl($concat_syntax_builder->getFunctionSyntax($multi_value, FormatSyntaxBuilderValueEnum::COLUMN));
