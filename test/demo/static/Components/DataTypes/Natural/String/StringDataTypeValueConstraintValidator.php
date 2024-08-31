<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueConstraintValidator;
use LWP\Components\Constraints\MaxSizeConstraint;

$string = "Hello World!";

$string_value = new StringDataTypeValueContainer($string);
var_dump($string_value::getConstraintValidatorClassName());

/* Constraint Validator */

#$constraint_validator = new StringDataTypeValueConstraintValidator($string_value);
$constraint_validator = $string_value->getConstraintValidatorClassObject();
$constraint_validator->addConstraint(new MaxSizeConstraint(10));

try {

    $constraint_validator->validate();

} catch (\Throwable $exception) {

    prl($exception::class);
    prl($exception->getMessage());
}
