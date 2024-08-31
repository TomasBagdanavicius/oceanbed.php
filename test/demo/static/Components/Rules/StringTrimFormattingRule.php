<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\StringTrimFormattingRule;

$options = [
    'side' => StringTrimFormattingRule::SIDE_BOTH,
    'mask' => 'AB',
    'mask_as' => StringTrimFormattingRule::MASK_AS_SUBSTRING,
    'repeatable' => true,
];
$string_trim_formatting_rule = new StringTrimFormattingRule($options);

var_dump($string_trim_formatting_rule->getSide());
var_dump($string_trim_formatting_rule->getMask());
var_dump($string_trim_formatting_rule->getMaskAs());
var_dump($string_trim_formatting_rule->getRepeatable());

/* Formatter */

$formatter = $string_trim_formatting_rule->getFormatter();

var_dump($formatter::class);
var_dump($formatter->format("ABAB Hello World! ABAB"));
