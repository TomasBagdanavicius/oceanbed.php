<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Natural\NaturalDataTypeFactory;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueConstraintValidator;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\Constraints\MaxSizeConstraint;
use LWP\Components\Constraints\ConstraintCollection;
use LWP\Components\Rules\StringTrimFormattingRule;

/* Raw To Data Type */

$string = " Hello World! ";

$string_value = NaturalDataTypeFactory::createDataTypeValueFromMixedTypeVariable($string);

/* Fomatting Rules */

$string_trim_formatting_rule = new StringTrimFormattingRule();
$string_trim_formatting_rule->getFormatter();

$string_value = $string_value->modifyByFormattingRule($string_trim_formatting_rule);
var_dump($string_value->getValue());

/* Constraint Validation */

$max_size_constraint = new MaxSizeConstraint(12);

$constraint_collection = new ConstraintCollection();
$constraint_collection->add($max_size_constraint);

$string_type_value = new StringDataTypeValueConstraintValidator($string_value, $constraint_collection);

$validation_result = $string_type_value->validate();
var_dump($validation_result);

/* Misc */

prl(StringDataTypeValueContainer::getDescriptorClassName());
