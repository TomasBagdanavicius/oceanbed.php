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
    'headers' => [
        'x-custom-header' => 'custom header value',
    ],
    // These will overwrite query params in the request URL.
    'query_params' => [
        'foo' => 'bar',
        'bar' => 'baz',
    ],
    'debug' => fopen(Demo\TEST_PATH . '/log/quick-curl-test.log', 'w'),
];
// If query params are available in options, query params in the URL will be overwritten.
$url_str = 'http://localhost/toolkit/request.php?param=invisible';
$url = new Url($url_str);

$client = new HttpClient();

$response = $client->get($url, $options);

include 'components/response-contents.php';
