<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\EmailAddress\EmailAddressDataTypeParser;
use LWP\Components\DataTypes\Custom\EmailAddress\EmailAddressDataTypeValueContainer;
use LWP\Network\Domain\DomainFileDataReader;
use LWP\Network\Domain\DomainDbDataReader;
use LWP\Filesystem\Path\PosixPath;

/* Domain Data Reader Global Variable */

// This will be added to the globals.
/* $domain_data_reader = function(): DomainFileDataReader {
    return new DomainFileDataReader(
        PosixPath::getFilePathInstance($_SERVER['DOCUMENT_ROOT'] . '/storage/downloads/Data/public-suffix-list/files/public_suffix_list.dat')
    );
}; */

// Needs to have the "domains" table populated.
$domain_data_reader = function (): DomainDbDataReader {
    require_once(Demo\TEST_PATH . '/database-link.php');
    return new DomainDbDataReader($database->getTable('domains'));
};

/* Value Container */

$value_string = "admin@lwis.net";
$value_container = new EmailAddressDataTypeValueContainer($value_string);
echo "Using value... ";
var_dump($value_string);

/* Parser */

$email_address_parser = new EmailAddressDataTypeParser($value_container, $value_container->domain_data_reader);

echo "Local part: ";
var_dump($email_address_parser->getLocalPart());
echo "Domain part: ";
var_dump($email_address_parser->getDomainPart()->__toString());
echo "Domain part class object: ", (\Demo\DEBUGGER)->formatter->namespaceToIdeHtmlLink($email_address_parser->getDomainPart()::class);
