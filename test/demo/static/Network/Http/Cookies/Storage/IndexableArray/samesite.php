<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Cookies\Cookies;
use LWP\Network\Http\Cookies\CookieStorage;
use LWP\Network\Uri\Url;
use LWP\Network\Domain\DomainFileDataReader;
use LWP\Filesystem\Path\PosixPath;

$domain_data_reader = new DomainFileDataReader(
    PosixPath::getFilePathInstance($_SERVER['DOCUMENT_ROOT'] . '/storage/downloads/Data/public-suffix-list/files/public_suffix_list.dat')
);

// Request URL.
$url = new Url("https://www.example.co.uk/dir/set.php", Url::HOST_VALIDATE_AS_DOMAIN_NAME, $domain_data_reader);
// Current site URL.
$url_match = new Url("https://www.example.co.uk/", Url::HOST_VALIDATE_AS_DOMAIN_NAME, $domain_data_reader);
$url_mismatch = new Url("https://www.diff.co.uk/", Url::HOST_VALIDATE_AS_DOMAIN_NAME, $domain_data_reader);

/* Strict mode. Matching sites. */

$cookie_str = "foo=bar; Domain=www.example.co.uk; Path=/; SameSite=Strict";
$data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

CookieStorage::validToGet($data, $url, $url_match);

/* Strict mode. Mismatching sites. */

try {

    $cookie_str = "foo=bar; Domain=www.example.co.uk; Path=/; SameSite=Strict";
    $data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

    CookieStorage::validToGet($data, $url, $url_mismatch);

} catch (Throwable $exception) {

    print "Expected error: " . $exception->getMessage() . PHP_EOL;
}

/* Lax mode. Matching request and action sites. */
/* Action site is the location of the trigger (eg. hyperlink) that fired the request. */

$cookie_str = "foo=bar; Domain=www.example.co.uk; Path=/; SameSite=Lax";
$data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

CookieStorage::validToGet($data, $url, $url_mismatch, $url_match);

/* Lax mode. Action site doesn't match request site. */

try {

    $cookie_str = "foo=bar; Domain=www.example.co.uk; Path=/; SameSite=Lax";
    $data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

    CookieStorage::validToGet($data, $url, $url_mismatch, $url_mismatch);

} catch (Throwable $exception) {

    print "Expected error: " . $exception->getMessage() . PHP_EOL;
}
