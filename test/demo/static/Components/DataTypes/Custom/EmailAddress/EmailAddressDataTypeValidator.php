<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\EmailAddress\EmailAddressDataTypeValidator;
use LWP\Network\Domain\DomainFileDataReader;
use LWP\Filesystem\Path\PosixPath;
use LWP\Network\Domain\DomainDbDataReader;

/* Domain Data Reader Global Variable */

// This will be added to the globals.
$domain_data_reader = new DomainFileDataReader(
    PosixPath::getFilePathInstance($_SERVER['DOCUMENT_ROOT'] . '/storage/downloads/Data/public-suffix-list/files/public_suffix_list.dat')
);
// Needs to have the "domains" table populated.
/* require_once (Demo\TEST_PATH . '/database-link.php');
$domain_data_reader = new DomainDbDataReader($database->getTable('domains')); */

$validator = new EmailAddressDataTypeValidator("admin@lwis.net", $domain_data_reader);

print "Result for \"{$validator->value}\": ";
var_dump($validator->validate());

$validator = new EmailAddressDataTypeValidator("Abc.example.com", $domain_data_reader);

print "Result for \"{$validator->value}\": ";
var_dump($validator->validate());
