<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Cookies\Cookies;

$header_field = Cookies::buildSetCookieHeaderField('foo', 'bar', [
    'domain' => 'localhost',
    'path' => '/technologies/PHP',
    'max-age' => 3600,
    'httponly' => true,
]);

print_r($header_field);
