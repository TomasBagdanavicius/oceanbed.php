<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(Demo\TEST_PATH . '/units/shared/array-collection-dataset.php');

use LWP\Components\Constraints\InDatasetConstraint;
use LWP\Components\Constraints\Validators\InDatasetConstraintValidator;

$in_dataset_constraint = new InDatasetConstraint($dataset, 'occupation');
$validator = $in_dataset_constraint->getValidator();

Demo\assert_true(
    $validator instanceof InDatasetConstraintValidator,
    "Invalid validator"
);
