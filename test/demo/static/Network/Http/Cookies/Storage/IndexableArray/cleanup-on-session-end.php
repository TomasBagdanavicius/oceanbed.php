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

$url = new Url("https://www.lwis.net/");

$cookie_storage = new CookieStorage($url);

/* Adds the first entry that should expire on session end. */

$cookie_str = "one=vienas";
$data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

$index_num = $cookie_storage->add($data);

/* Add an entry that should not expire on session end. */

$cookie_str = "two=du; Max-Age=1";
$data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

$index_num = $cookie_storage->add($data);

/* Add another entry that should expire. */

$cookie_str = "three=trys";
$data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

$index_num = $cookie_storage->add($data);

/* Clean up all entries that should be valid until session end. */

$cookie_storage->clearSessionEntries();

/* Final results. Eventually, all entries except the second one should be removed. */

print_r($cookie_storage->getIndexableCollection()->toArray());
