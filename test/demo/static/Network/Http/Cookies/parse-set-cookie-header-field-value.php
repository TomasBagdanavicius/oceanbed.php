<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Cookies\Cookies;

$field_value = 'foo=bar; Domain=localhost; path=/dir/subdir; HttpOnly';

$parse = Cookies::parseSetCookieHeaderFieldValue($field_value);

var_dump($parse);
