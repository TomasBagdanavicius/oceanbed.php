<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\StringTrimFormattingRule;
use LWP\Database\SyntaxBuilders\StringTrimSyntaxBuilder;

$string = "  Trim  ";

/* Formatting Rule */

$options = [
    'side' => StringTrimFormattingRule::SIDE_BOTH,
    'mask' => chr(32), // A single whitespace character.
    'mask_as' => StringTrimFormattingRule::MASK_AS_SUBSTRING,
    'repeatable' => false,
];
$string_trim_formatting_rule = new StringTrimFormattingRule($options);

/* Syntax Builder */

$string_trim_syntax_builder = new StringTrimSyntaxBuilder($string_trim_formatting_rule);

Demo\assert_true(
    $string_trim_syntax_builder instanceof StringTrimSyntaxBuilder,
    "Unexpected result"
);
