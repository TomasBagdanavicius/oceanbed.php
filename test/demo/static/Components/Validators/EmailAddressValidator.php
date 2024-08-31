<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Validators\EmailAddressValidator;
use LWP\Network\Exceptions\InvalidEmailAddressException;
use LWP\Network\Domain\DomainFileDataReader;
use LWP\Network\Domain\DomainDbDataReader;
use LWP\Filesystem\Path\PosixPath;

$domain_data_reader = new DomainFileDataReader(
    PosixPath::getFilePathInstance($_SERVER['DOCUMENT_ROOT'] . '/storage/downloads/Data/public-suffix-list/files/public_suffix_list.dat')
);

// Needs to have the "domains" table populated.
/* require_once (Demo\TEST_PATH . '/database-link.php');
$domain_data_reader = new DomainDbDataReader($database->getTable('domains')); */

$validator = new EmailAddressValidator("admin@lwis.net", $domain_data_reader);
print "Result: ";
var_dump($validator->validate());

/* Error Simulation */

try {

    $validator->value = "admin@lwis.netas"; // Mind invalid top level domain name.
    $validator->validate();

} catch (InvalidEmailAddressException $exception) {

    prl("Expected error: " . $exception->getMessage());
}
