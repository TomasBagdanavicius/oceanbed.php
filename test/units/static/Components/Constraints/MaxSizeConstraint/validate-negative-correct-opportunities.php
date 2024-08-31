<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Constraints\MaxSizeConstraint;
use LWP\Components\Constraints\Violations\MaxSizeConstraintViolation;

$in_set_constraint = new MaxSizeConstraint(10);
$validator = $in_set_constraint->getValidator();
$validator_result = $validator->validate(22);

Demo\assert_true(
    $validator_result->getCorrectionOpportunities() === [10, 9, 8],
    "Unexpected validation result"
);
