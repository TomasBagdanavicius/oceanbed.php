<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\StringTrimFormattingRule;

$options = [
    'side' => 'both',
    'mask' => "sA",
    'mask_as' => 'chars',
    'repeatable' => true,
];

$string_trim_formatting_rule = new StringTrimFormattingRule($options);

/* Formatter */

$formatter = $string_trim_formatting_rule->getFormatter();

var_dump($formatter->format("As Hello World! sA"));
var_dump($formatter->format("AsAs Hello World! AsAs"));
var_dump($formatter->format("sAAs Hello World! sAAs"));
var_dump($formatter->format("aS Hello World! Sa")); // Case-sensitive as is the "trim" function in PHP.
