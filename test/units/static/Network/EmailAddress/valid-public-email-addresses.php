<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\EmailAddress;
use LWP\Network\Domain\DomainFileDataReader;
use LWP\Filesystem\Path\PosixPath;

$domain_data_reader = new DomainFileDataReader(
    PosixPath::getFilePathInstance(
        $_SERVER['DOCUMENT_ROOT']
        . '/storage/downloads/Data/public-suffix-list/files'
        . '/public_suffix_list.dat'
    )
);

$email_addresses = [
    'simple@example.com',
    'very.common@example.com',
    'disposable.style.email.with+symbol@example.com',
    'other.email-with-hyphen@example.com',
    'fully-qualified-domain@example.com',
    'user.name+tag+sorting@example.com',
    'x@example.com',
    'example-indeed@strange-example.com',
    '" "@example.org',
    '"john..doe"@example.org',
    'mailhost!username@example.org',
    'user%example.com@example.org',
    'Pelé@example.com',
    '我買@屋企.香港',
    'медведь@с-балалайкой.рф',
    'संपर्क@डाटामेल.भारत',
];

$no_errors = true;

foreach ($email_addresses as $email_address_str) {

    try {

        $email_address = new EmailAddress(
            $email_address_str,
            EmailAddress::DOMAIN_VALIDATE_AS_PUBLIC,
            $domain_data_reader
        );

    } catch (\Throwable $exception) {

        $no_errors = false;
        break;
    }
}

Demo\assert_true(
    $no_errors,
    "Email address "
    . $email_address_str
    . " is valid and should not cause errors"
);
