<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\NumberFormatter;
use LWP\Components\Rules\NumberFormattingRule;
use LWP\Components\DataTypes\Custom\Number\NumberDataTypeValueContainer;

/* Formatting Rule */

$options = [
    'fractional_part_length' => 2,
    'fractional_part_separator' => ',',
    'integer_part_group_length' => 3,
    'integer_part_group_separator' => ' ',
    'zerofill' => null,
    'integer_part_trailing_group_extended' => false,
];
$number_formatting_rule = new NumberFormattingRule($options);

/* Formatter */

$number_formatter = new NumberFormatter($number_formatting_rule);

// Unsigned Integer
var_dump($number_formatter->format(123456.78));
// Signed Integer
#var_dump( $number_formatter->format(-123456) );
// Float (longer fraction)
#var_dump( $number_formatter->format(1000.123) );
// Float (shorter fraction)
#var_dump( $number_formatter->format(1000.1) );
// String
#var_dump( $number_formatter->format("1,000,000.0998E2") );
// NumberDataTypeValue
/* $number_data_type_value = new NumberDataTypeValueContainer("-1'234'567,90");
var_dump( $number_formatter->format($number_data_type_value) ); */
