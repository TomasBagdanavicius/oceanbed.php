<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\UriReference;

try {

    $uris = [
        0 => 'http://username:password@example.com:80/dir?one[]=Vienas&two=Du&two=Zwei#fragment',
        // Relative.
        20 => '/one/two/three',
    ];

    $uri = new UriReference($uris[20]);

    print_r($uri->getParts());

} catch (\Throwable $exception) {

    die($exception->getMessage());
}
