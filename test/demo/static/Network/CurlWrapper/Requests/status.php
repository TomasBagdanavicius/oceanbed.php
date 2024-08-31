<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\Url;
use LWP\Network\CurlWrapper\Client as HttpClient;

$options = [
    'follow_location' => false,
];

$url = new Url('http://localhost/bin/HTTP/location-302.php');

$client = new HttpClient();

$status_code = $client->status($url, $options);

var_dump($status_code);
