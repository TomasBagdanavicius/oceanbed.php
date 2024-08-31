<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\Url;
use LWP\Network\Request;

$url_str = 'https://www.lwis.net/';

$url = new Url($url_str);

$certificate_info = Request::getSslCertificateInfo($url);

if ($certificate_info) {

    print "Has valid SSL certificate: ";
    var_dump(Request::verifyHostnameAgainstSSLCertificate($url->getHost(), $certificate_info));

    print_r($certificate_info);

} else {

    die("Could not get certificate info.");
}
