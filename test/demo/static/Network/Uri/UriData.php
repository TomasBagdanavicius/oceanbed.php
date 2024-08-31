<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\UriData;

$uris = [
    0 => 'data:image/png;charset=UTF-8;page=21;base64,VGhpcyBpcyBjbGFzc2lmaWVkIGluZm9ybWF0aW9uLg==',
    1 => 'data:text/plain;charset=UTF-8;page=21,the%20data:1234,5678',
    2 => 'data:,',
];

$uri_data = new UriData($uris[0]);

var_dump($uri_data->getData());
