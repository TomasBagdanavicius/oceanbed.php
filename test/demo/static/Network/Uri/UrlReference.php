<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Domain\DomainFileDataReader;
use LWP\Network\Domain\DomainDbDataReader;
use LWP\Network\Uri\UrlReference;
use LWP\Filesystem\Path\PosixPath;

$urls = [
    0 => 'https://xn--bcher-kva.de/',
    1 => 'https://www.bücher.de/',
    2 => 'www.tspmi.vu.lt/as/tu?vienas[0]=One#frag',
    4 => 'http://localhost',
    5 => 'http://[2001:0db8:85a3:08d3:1319:8a2e:0370:7334]',
    6 => 'https://www.lwis.net',
    // Relative
    10 => 'lwis.net',
    11 => 'domain.com?one=Earth&two=eyes',
    12 => 'domain.com#fragment',
    13 => '//example.com/one/two/three',
    14 => '//new:ad@lwis.net:80/as',
    15 => '/foo/bar',
];

$domain_data_reader = new DomainFileDataReader(
    PosixPath::getFilePathInstance($_SERVER['DOCUMENT_ROOT'] . '/storage/downloads/Data/public-suffix-list/files/public_suffix_list.dat')
);
// Needs to have the "domains" table populated.
/* require_once (Demo\TEST_PATH . '/database-link.php');
$domain_data_reader = new DomainDbDataReader($database->getTable('domains')); */

$url = new UrlReference($urls[10], UrlReference::HOST_VALIDATE_AS_DOMAIN_NAME, $domain_data_reader);

var_dump($url->getUri());
#print_r( $url->getParts() );
