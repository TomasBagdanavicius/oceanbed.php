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

$url = new Url("http://www.lwis.net/foo");

$cookie_storage = new CookieStorage();

/* Valid. */

$cookie_str = "foo=bar; Path=/bar; HttpOnly";
$data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

CookieStorage::validToSet($data, $url);

/* Invalid. */

try {

    $cookie_str = "foo=bar; Path=/bar; HttpOnly; Secure";
    $data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

    CookieStorage::validToSet($data, $url);

} catch (Throwable $exception) {

    print "Expected error: " . $exception->getMessage() . PHP_EOL;
}
