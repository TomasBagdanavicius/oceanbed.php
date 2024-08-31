<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Violations\DomainNameViolation;

$url = "https://www.example.com";
$not_allowed_domain = "example.com";

if (str_ends_with($url, $not_allowed_domain)) {

    $divisible_by_violation = new DomainNameViolation($url, $not_allowed_domain);

    prl($divisible_by_violation->getErrorMessageString());
    #prl( $divisible_by_violation->getExtendedErrorMessageString() );
    #prl( $divisible_by_violation->getErrorMessage()->text );
    print_r($divisible_by_violation->getCorrectionOpportunities());
    #$divisible_by_violation->throwException();

} else {

    prl("OK");
}
