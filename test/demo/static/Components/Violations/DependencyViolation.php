<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Violations\DependencyViolation;

$var_1;
$var_2;
// Dependent upon "var_1" and "var_2".
$var_3;

// If both or any of these variables are not set.
if (!isset($var_1, $var_2)) {

    $dependency_violation = new DependencyViolation(['var_1', 'var_2'], 'var_3');

    prl($dependency_violation->getErrorMessageString());
    #prl( $dependency_violation->getExtendedErrorMessageString() );
    #prl( $dependency_violation->getErrorMessage()->text );
    #$dependency_violation->throwException();

} else {

    prl("OK");
}
