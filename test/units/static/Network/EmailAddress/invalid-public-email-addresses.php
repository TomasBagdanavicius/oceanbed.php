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
    '二ノ宮@黒川.日本',
];

$has_errors = true;

foreach ($email_addresses as $email_address_str) {

    try {

        $email_address = new EmailAddress(
            $email_address_str,
            EmailAddress::DOMAIN_VALIDATE_AS_PUBLIC,
            $domain_data_reader
        );

        $has_errors = false;
        break;

    } catch (\Throwable $exception) {

    }
}

Demo\assert_true(
    $has_errors,
    "Email address "
    . $email_address_str
    . " is invalid and should not be treated as valid"
);
