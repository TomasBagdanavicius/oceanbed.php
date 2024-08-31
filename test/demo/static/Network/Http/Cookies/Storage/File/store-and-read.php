<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Cookies\Cookies;
use LWP\Network\Http\Cookies\CookieStorage;
use LWP\Network\Http\Cookies\CookieFileStorage;
use LWP\Network\Uri\Url;

$url = new Url("https://www.lwis.net/");

$cookie_file_storage = new CookieFileStorage(__DIR__ . '/storage.json', $url);

$cookie_file_storage->truncate();

/* Add an entry that will expire after 1 sec. */

$cookie_str = "one=vienas; Max-Age=1";
$data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

$index_num = $cookie_file_storage->add($data);

/* Add another entry. */

$cookie_str = "two=du; Domain=.www.lwis.net; Max-Age=3";
$data = Cookies::parseSetCookieHeaderFieldValue($cookie_str);

$index_num = $cookie_file_storage->add($data);

/* Save changes and wait. */

$cookie_file_storage->save();

sleep(2);

/* Load file again. */

$cookie_file_storage = new CookieFileStorage(__DIR__ . '/storage.json', $url);

/* It should reject the first entry and accept the second one. */

print_r($cookie_file_storage->getData());
