<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Conditions\Condition;
use LWP\Components\Constraints\InDatasetConstraint;
use LWP\Components\Constraints\Validators\InDatasetConstraintValidator;
use LWP\Components\Violations\InSetViolation;

$table = $database->getTable('temp');

$in_dataset_constraint = new InDatasetConstraint($table, 'custom');

echo "Chosen container name: ";
var_dump($in_dataset_constraint->container_name);

$validator = $in_dataset_constraint->getValidator();
$validate = $validator->validate(['C1', 'C2', 'C9']);

if ($validate === true) {

    echo "Result: ";
    prl("OK");

} else {

    echo "Result: ";
    prl("Violation");

    echo "Is instance of InSetViolation: ";
    var_dump($validate instanceof InSetViolation);

    $violation = $validate;

    var_dump($violation->getErrorMessage()->text);
}

/* With Conditions */

$condition = new Condition('custom_4', 1, data: [
    // This is required when using tables.
    'parameterize' => true,
]);

$condition_group = ConditionGroup::fromCondition($condition);

$in_dataset_constraint = new InDatasetConstraint($table, 'custom', $condition_group);
$validator = $in_dataset_constraint->getValidator();
$validate = $validator->validate(['C1', 'C2', 'C9']);

if ($validate === true) {

    echo "Result: ";
    prl("OK");

} else {

    echo "Result: ";
    prl("Violation");

    $violation = $validate;

    var_dump($violation->getErrorMessage()->text);
}
