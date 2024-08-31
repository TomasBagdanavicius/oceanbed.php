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

/* Set "max-age" to one second. */

$cookie_str = "one=Vienas; Max-Age=1";
$cookie_data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

$index_num = $cookie_storage->add($cookie_data);

/* Should not contain any expired entries yet. */

var_dump($cookie_storage->containsExpiredEntries());

/* Wait more than one second. */

sleep(2);

/* Should be expired by now. */

var_dump($cookie_storage->containsExpiredEntries());
