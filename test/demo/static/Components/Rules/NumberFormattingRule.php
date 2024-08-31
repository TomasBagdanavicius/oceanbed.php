<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\NumberFormattingRule;

$options = [
    'fractional_part_length' => 2,
    'fractional_part_separator' => '.',
    'integer_part_group_separator' => ',',
    'integer_part_group_length' => 3,
    'integer_part_trailing_group_extended' => false,
    'zerofill' => null,
];

$number_formatting_rule = new NumberFormattingRule($options);

echo "Fractional part length: ";
var_dump($number_formatting_rule->getFractionalPartLength());
echo "Fractional part separator: ";
var_dump($number_formatting_rule->getFractionalPartSeparator());
echo "Integer part group separator: ";
var_dump($number_formatting_rule->getIntegerPartGroupSeparator());
echo "Integer part group length: ";
var_dump($number_formatting_rule->getIntegerPartGroupLength());
echo "Zerofill length: ";
var_dump($number_formatting_rule->getZerofill());
echo "Is integer part trailing group extended? ";
var_dump($number_formatting_rule->isIntegerPartTrailingGroupExtended());

/* Formatter */

$formatter = $number_formatting_rule->getFormatter();

var_dump($formatter::class);
var_dump($formatter->format(12345));
