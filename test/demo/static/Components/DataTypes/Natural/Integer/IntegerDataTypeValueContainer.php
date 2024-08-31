<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeError;
use LWP\Components\DataTypes\Natural\Integer\ToIntegerDataTypeConverter;

$value = 12345;

$value_container = new IntegerDataTypeValueContainer($value);
var_dump($value_container->getValue());
var_dump($value_container->getDescriptorClassName());
#var_dump( $value_container->getDescriptorClassObject() );
var_dump($value_container->getConstraintValidatorClassName());
#var_dump( $value_container->getConstraintValidatorClassObject() );

/* Incorrect Type */

try {
    $value_container = new IntegerDataTypeValueContainer("Hello World!");
} catch (DataTypeError $error) {
    prl($error->getMessage());
}

/* Create From */

$value_container = ToIntegerDataTypeConverter::convert("12345.67");
var_dump($value_container->getValue());
var_dump($value_container->modifyByDeduction(2345)->getValue());

/* Obtain Parser */

$integer_parser = $value_container->getParser();
var_dump($integer_parser::class);
var_dump($integer_parser->getLength());
var_dump($integer_parser->dividesBy(5));
