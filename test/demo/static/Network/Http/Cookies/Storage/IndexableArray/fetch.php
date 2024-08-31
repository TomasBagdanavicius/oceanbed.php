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

$url = new Url("http://www.lwis.net/");

$cookie_storage = new CookieStorage($url);

/* Adds first valid entry. Default path. */

$cookie_str = "one=vienas; HttpOnly; Max-Age=1";
$data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

$index_num = $cookie_storage->add($data);

/* Adds a second entry. Sets path to custom /tmp. */

$cookie_str = "two=du; Path=/tmp; HttpOnly; Max-Age=1";
$data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

$index_num = $cookie_storage->add($data);

/* Adds a second entry for a different domain name. */

$cookie_str = "three=trys; Path=/; HttpOnly; Max-Age=1";
$data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

$index_num = $cookie_storage->add($data, [
    'url' => new Url("https://www.lwis.net/"),
]);

pre($cookie_storage->getIndexableCollection()->toArray());

/* This should yield one entry. Since entry with /tmp is above root, it won't send on paths below. */

$collection = $cookie_storage->fetch($url);
print_r($collection->toArray());

/* The below should yield 2 entries. All entries on /tmp path as well as below. */

$collection = $cookie_storage->fetch(new Url('https://www.lwis.net/tmp/'));
print_r($collection->toArray());
