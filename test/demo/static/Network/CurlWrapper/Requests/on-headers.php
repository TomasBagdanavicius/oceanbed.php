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
    'throw_errors' => true,
    'on_headers' => function (ResponseBuffer $response_buffer, int $header_lines_count, int $redirections_count) {

        print "Header lines count: ";
        var_dump($header_lines_count);

        $response_headers = $response_buffer->getResponseHeaders();

        print_r($response_headers->toArray());

        if ($response_headers->containsKey('location')) {
            throw new Exception("Bad response.");
        }

    },
];

$url_str = 'http://localhost/bin/HTTP/location-302.php';
$url = new Url($url_str);

$client = new HttpClient();

$response = $client->get($url, $options);

include 'components/response-contents.php';
