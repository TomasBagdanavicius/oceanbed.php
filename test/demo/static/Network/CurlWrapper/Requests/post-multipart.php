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

$options = [
    'debug' => fopen(Demo\TEST_PATH . '/log/quick-curl-test.log', 'w+'),
    'multipart_data' => [
        'one' => 'Earth',
        [
            'name' => 'two[]',
            'contents' => 'Eyes',
        ],
        [
            'name' => 'two[]',
            'contents' => 'Ears',
        ],
        'three' => [
            'contents' => 'Buttons',
        ],
    ],
];
$url_str = 'http://localhost/toolkit/request.php';
$url = new Url($url_str);
$client = new HttpClient();
$response = $client->post($url, $options);

include 'components/response-contents.php';
