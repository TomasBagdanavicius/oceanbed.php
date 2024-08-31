<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Violations\DataTypeViolation;

$set = [
    'number',
    'ip_address',
];

$value = 'email_address';

// Array intersection is also supported.
if (!in_array($value, $set)) {

    $data_type_violation = new DataTypeViolation($set, $value, $value);

    prl($data_type_violation->getErrorMessageString());
    #prl( $data_type_violation->getExtendedErrorMessageString() );
    #prl( $data_type_violation->getErrorMessage()->text );
    print_r($data_type_violation->getCorrectionOpportunities());
    #$data_type_violation->throwException();

} else {

    prl("OK");
}
