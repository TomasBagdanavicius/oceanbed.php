<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\StringTrimFormattingRule;
use LWP\Components\Rules\StringTrimFormatter;

$options = [
    'side' => StringTrimFormattingRule::SIDE_BOTH,
    'mask' => '*_-',
];
$string_trim_formatting_rule = new StringTrimFormattingRule($options);
$formatter = $string_trim_formatting_rule->getFormatter();
$formatted_string = $formatter->format("_-*Hello World!*-_");

Demo\assert_true($formatted_string === "Hello World!", "Unexpected result");
