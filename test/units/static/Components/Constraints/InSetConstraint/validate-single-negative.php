<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Constraints\InSetConstraint;
use LWP\Components\Constraints\Violations\InSetConstraintViolation;

$set = [
    'one',
    'two',
    'three',
    'four',
    'five',
];

$in_set_constraint = new InSetConstraint($set);
$validator = $in_set_constraint->getValidator();
$validation_result = $validator->validate('six');

Demo\assert_true(
    $validation_result instanceof InSetConstraintViolation,
    "Incorrect validation result"
);
