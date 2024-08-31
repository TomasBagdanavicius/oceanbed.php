<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(Demo\TEST_PATH . '/units/shared/array-collection-dataset.php');

use LWP\Components\Constraints\InDatasetConstraint;
use LWP\Components\Constraints\Violations\InDatasetConstraintViolation;

$in_dataset_constraint = new InDatasetConstraint($dataset, 'occupation');
$validator = $in_dataset_constraint->getValidator();
$validation_result = $validator->validate(["Teacher", "Lawyer", "Driver", "Pilot"]);

Demo\assert_true(
    $validation_result instanceof InDatasetConstraintViolation,
    "Incorrect validation result"
);
