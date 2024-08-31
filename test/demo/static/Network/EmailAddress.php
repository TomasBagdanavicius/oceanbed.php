<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\EmailAddress;
use LWP\Network\Domain\DomainFileDataReader;
use LWP\Network\Domain\DomainDbDataReader;
use LWP\Filesystem\Path\PosixPath;

$domain_data_reader = new DomainFileDataReader(
    PosixPath::getFilePathInstance($_SERVER['DOCUMENT_ROOT'] . '/storage/downloads/Data/public-suffix-list/files/public_suffix_list.dat')
);
// Needs to have the "domains" table populated.
/* require_once (Demo\TEST_PATH . '/database-link.php');
$domain_data_reader = new DomainDbDataReader($database->getTable('domains')); */

$email_address = new EmailAddress('john.doe@', EmailAddress::DOMAIN_VALIDATE_AS_PUBLIC, $domain_data_reader);

var_dump($email_address->getLocalPart());
var_dump($email_address->getDomainPart()->__toString());

/* Validates Local Part */

$local_part = '"John Doe"';

var_dump(EmailAddress::validateLocalPart($local_part));
