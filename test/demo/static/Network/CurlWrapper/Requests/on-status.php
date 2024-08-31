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
    'on_status' => function (ResponseBuffer $response_buffer, int $redirections_count) {

        // Get full status line info.
        var_dump($response_buffer->getStatusLine()->toArray());

        // The below will abort this request.
        if ($response_buffer->getStatusCode() !== 200) {
            throw new \Exception("Bad response.");
        }

    },
];

$url_str = 'http://localhost/bin/HTTP/location-302.php';
$url = new Url($url_str);

$client = new HttpClient();

$response = $client->get($url, $options);

include 'components/response-contents.php';
