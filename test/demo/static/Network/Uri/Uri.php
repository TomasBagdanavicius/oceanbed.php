<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\Uri;

try {

    $uris = [
        0 => 'http://username:password@example.com:80/dir?one[]=Vienas&two=Du&two=Zwei#fragment',
        // Invalid.
        20 => '/one/two/three',
        21 => 'news:',
        22 => '?foo=bar',
    ];

    $uri = new Uri($uris[0]);

    print $uri;
    print PHP_EOL;
    print_r($uri->getParts());

} catch (Throwable $exception) {

    die($exception->getMessage());
}
