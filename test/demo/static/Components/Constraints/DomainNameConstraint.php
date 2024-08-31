<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Constraints\DomainNameConstraint;
use LWP\Components\Violations\DomainNameViolation;
use LWP\Network\EmailAddress;
use LWP\Network\Domain\Domain;
use LWP\Network\Domain\DomainFileDataReader;
use LWP\Filesystem\Path\PosixPath;
use LWP\Filesystem\Path\PathEnvironmentRouter;

$path = PathEnvironmentRouter::getStaticInstance();
$domain_data_reader = new DomainFileDataReader(
    $path::getFilePathInstance($_SERVER['DOCUMENT_ROOT'] . '/storage/downloads/Data/public-suffix-list/files/public_suffix_list.dat')
);
$domain = new Domain('example.com', $domain_data_reader);
$domain_name_constraint = new DomainNameConstraint($domain);

echo "Value: ";
var_dump($domain_name_constraint->getValue());

echo "Definition: ";
print_r($domain_name_constraint->getDefinition());

$email_address = new EmailAddress('john.doe@domain.com', EmailAddress::DOMAIN_VALIDATE_NONE);
$validator = $domain_name_constraint->getValidator();
$result = $validator->validate($email_address);

if ($result === true) {

    prl("OK");

} else {

    print "Violation" . "\n";

    var_dump($result instanceof DomainNameViolation);

    $violation = $result;

    var_dump($violation->getErrorMessageString());
    var_dump($violation->getCorrectionOpportunities());
}
