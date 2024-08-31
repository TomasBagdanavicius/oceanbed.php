<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeError;
use LWP\Components\Rules\StringTrimFormattingRule;

$value = "Hello World!";

prl("Setting value... " . $value);
$value_container = new StringDataTypeValueContainer($value);
echo "Getting value: ";
var_dump($value_container->getValue());
echo "Descriptor class name: ";
var_dump($value_container->getDescriptorClassName());
#echo "Descriptor: "; var_dump( $value_container->getDescriptorClassObject() );
#echo "Constraint validator class name: "; var_dump( $value_container->getConstraintValidatorClassName() );
#echo "Constraint validator: "; var_dump( $value_container->getConstraintValidatorClassObject() );
#echo "Is empty: "; var_dump( $value_container->isEmpty() );

/* Incorrect Type */

try {
    $value_container = new StringDataTypeValueContainer(123);
} catch (DataTypeError $error) {
    prl("Expected error: " . $error->getMessage());
}

/* Obtain Parser */

$parser = $value_container->getParser();
var_dump($parser::class);
var_dump($parser->getLength());

/* Modify By Formatting Rule */

$value_container = new StringDataTypeValueContainer(" Hello World! ");

$string_trim_formatting_rule = new StringTrimFormattingRule();
var_dump($string_trim_formatting_rule->getFormatter()::class);

$value_container = $value_container->modifyByFormattingRule($string_trim_formatting_rule);
var_dump($value_container->getValue());

/* Create From Definition Array */

$value_container = StringDataTypeValueContainer::fromDefinitionArray([
    'type' => 'string',
    'description' => 'Definition.',
], "Value");
var_dump($value_container->getValue());
