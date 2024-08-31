<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\Number\ToNumberDataTypeConverter;

$converter = new ToNumberDataTypeConverter();

/* Create From */

// From float/double.
$number_value = $converter::convert(123.45);
echo "From float (fractional part): ";
var_dump($number_value->getParser()->getFractionalPart());

// From integer.
$number_value = $converter::convert(12345);
print "From integer (length): ";
var_dump($number_value->getParser()->getLength());

// From number string.
$number_value = $converter::convert("123 456.78");
echo "From number string (integer part): ";
var_dump($number_value->getParser()->getInteger());

// From number string containing an exponent.
$number_value = $converter::convert("123.45e2");
echo "From number string containing exponent (exponent part): ";
var_dump($number_value->getParser()->getExponent());
