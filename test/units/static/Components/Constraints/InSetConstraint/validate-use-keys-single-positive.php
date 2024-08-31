<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Constraints\InSetConstraint;

$set = [
    'one' => "Vienas",
    'two' => "Du",
    'three' => "Trys",
    'four' => "Keturi",
    'five' => "Penki",
];

$in_set_constraint = new InSetConstraint($set, use_keys: true);
$validator = $in_set_constraint->getValidator();
$validation_result = $validator->validate('three');

Demo\assert_true($validation_result, "Given value is in fact in the set");
