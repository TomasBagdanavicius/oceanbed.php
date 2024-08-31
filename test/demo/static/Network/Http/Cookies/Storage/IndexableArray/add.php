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

$url = new Url("http://lwis.net/");

$cookie_storage = new CookieStorage($url);

/* Adds first valid entry. */

$cookie_str = "foo=bar; HttpOnly; Max-Age=1";
$data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

$index_num = $cookie_storage->add($data);

/* Rejects an attempt to add new entry. */

$cookie_str = "foo=baz; Max-Age=0";
$data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

try {

    $index_num = $cookie_storage->add($data);

} catch (Throwable $exception) {

    print "Expected error: " . $exception->getMessage() . PHP_EOL;
}

/* Adds and replaces an existing entry. */

$cookie_str = "foo=baz; Path=/; HttpOnly; Max-Age=2";
$data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

$index_num = $cookie_storage->add($data);

/* Final results. Should yield one entry. */

print_r($cookie_storage->getIndexableCollection()->toArray());
