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

$url = new Url("https://www.lwis.net/foo/");

$cookie_storage = new CookieStorage();

/* Valid. */

$cookie_str = "foo=bar; Domain=.lwis.net; Path=/foo";
$data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

CookieStorage::validToGet($data, $url);

/* Invalid. */

try {

    $cookie_str = "foo=bar; Domain=lwis.net; Path=/";
    $data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

    CookieStorage::validToGet($data, $url);

} catch (Throwable $exception) {

    print "Expected error: " . $exception->getMessage() . PHP_EOL;
}
