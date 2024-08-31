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
    'debug' => fopen(Demo\TEST_PATH . '/log/quick-curl-test.log', 'w+'),
];
$url = new Url('https://www.lwis.net/');
$client = new HttpClient();
$options = $client->options($url, $options);

var_dump($options);
