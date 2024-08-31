<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\Url;
use LWP\Network\CurlWrapper\Client as HttpClient;
use LWP\Network\CurlWrapper\ResponseBuffer;
use LWP\Network\Http\Exceptions\RequestErrorException;

$options = [
    'throw_errors' => true,
    'connect_timeout' => 1000,
    'debug' => fopen(Demo\TEST_PATH . '/log/quick-curl-test.log', 'w+'),
];
$url_str = 'https://www.qwerty123456.lt/';
$url = new Url($url_str);
$client = new HttpClient();
$response = $client->get($url, $options);

include 'components/response-contents.php';
