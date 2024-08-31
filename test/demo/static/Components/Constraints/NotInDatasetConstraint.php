<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

use LWP\Components\Constraints\NotInDatasetConstraint;
use LWP\Components\Constraints\Validators\NotInDatasetConstraintValidator;
use LWP\Components\Violations\NotInSetViolation;

$table = $database->getTable('temp');

$not_in_dataset_constraint = new NotInDatasetConstraint($table, 'custom');

echo "Chosen container name: ";
var_dump($not_in_dataset_constraint->container_name);

$validator = new NotInDatasetConstraintValidator($not_in_dataset_constraint);

$validate = $validator->validate(['C1', 'C2', 'C9', 'C10']);

if ($validate === true) {

    prl("OK");

} else {

    prl("Violation");

    echo "Is instance of InSetViolation: ";
    var_dump($validate instanceof NotInSetViolation);

    $violation = $validate;

    var_dump($violation->getErrorMessage()->text);
}
