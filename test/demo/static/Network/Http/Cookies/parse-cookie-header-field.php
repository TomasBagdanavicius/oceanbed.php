<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Cookies\Cookies;

// Typical.
$str = 'Cookie: foo=bar';
// Minimal. Part string should come back as value.
#$str = 'Cookie: foo';
// Multiple name-value pairs.
#$str = 'Cookie: foo=bar; name2=value2; name3=value3';
// One value absent.
#$str = 'Cookie: foo=bar; name2=; name3=value3';

print_r(Cookies::parseCookieHeaderField($str));
