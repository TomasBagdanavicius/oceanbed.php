<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\Number\NumberDataTypeParser;
use LWP\Components\Rules\NumberFormattingRule;

/* Formatting Rule To Parse By */

$options = [
    'fractional_part_separator' => ',',
    'integer_part_group_separator' => ' ',
    'integer_part_group_length' => 3,
    'integer_part_trailing_group_extended' => false,
    'zerofill' => 9, // If above 0 or not null, leading zeros will be allowed.
    // Option 'fractional_part_length' doesn't play any role in parsing.
];

$number_formatting_rule = new NumberFormattingRule($options);

/* Parser */

$number_parser = new NumberDataTypeParser("-12 456,78e3", $number_formatting_rule); // Parse by formatting rule.

var_dump($number_parser->__toString());
print "Integer part: ";
var_dump($number_parser->getIntegerPart());
print "Fractional part: ";
var_dump($number_parser->getFractionalPart());
print "Exponent: ";
var_dump($number_parser->getExponent());
print "Float: ";
var_dump($number_parser->getFloat());
print "Integer: ";
var_dump($number_parser->getInteger());
print "Integer length: ";
var_dump($number_parser->getIntegerLength());
print "Fraction: ";
var_dump($number_parser->getFraction());
print "Integer data type value object: ";
var_dump($number_parser->getIntegerDataTypeObject()::class);
print "Is signed? ";
var_dump($number_parser->isSigned());
print "Leading zeros length: ";
var_dump($number_parser->getLeadingZerosLength());

#print_r( NumberDataTypeParser::splitAtENotation("9.045e12") );
#print_r( NumberDataTypeParser::splitAtFractionalPartSeparator("9.045", '.') );
