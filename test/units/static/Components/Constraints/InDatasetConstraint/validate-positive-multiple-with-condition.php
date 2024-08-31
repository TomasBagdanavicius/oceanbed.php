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
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;

$condition = new Condition('name', "John");
$condition_group = ConditionGroup::fromCondition($condition);

$in_dataset_constraint = new InDatasetConstraint($dataset, 'occupation', $condition_group);
$validator = $in_dataset_constraint->getValidator();
$validation_result = $validator->validate(["Teacher", "Architect"]);

Demo\assert_true($validation_result, "Incorrect validation result");
