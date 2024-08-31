<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\Url;
use LWP\Network\Domain\DomainFileDataReader;
use LWP\Network\Domain\DomainDbDataReader;
use LWP\Filesystem\Path\PosixPath;

$urls = [
    0 => 'https://www.example.com/',
    1 => 'https://www.example.com:443/',
    // Invalid. These can work as URL references though.
    20 => '/foo/bar',
    21 => 'domain.com/foo/bar',
    22 => '//domain.com/foo/bar',
];

$domain_data_reader = new DomainFileDataReader(
    PosixPath::getFilePathInstance($_SERVER['DOCUMENT_ROOT'] . '/storage/downloads/Data/public-suffix-list/files/public_suffix_list.dat')
);
// Needs to have the "domains" table populated.
/* require_once (Demo\TEST_PATH . '/database-link.php');
$domain_data_reader = new DomainDbDataReader($database->getTable('domains')); */

$url = new Url($urls[0], Url::HOST_VALIDATE_AS_DOMAIN_NAME, $domain_data_reader);

var_dump($url->getUrl()->__toString());
