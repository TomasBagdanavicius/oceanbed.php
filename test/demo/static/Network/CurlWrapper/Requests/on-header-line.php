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
use LWP\Network\Headers;

$options = [
    'on_header_line' => function (string $header_line, ResponseBuffer $response_buffer, int $line_number, int $redirections_count) {

        // Exclude the first status line.
        if ($line_number > 1) {

            // Parses field string into name and value parts.
            $header_field_parts = Headers::parseField($header_line);

            // Exception condition.
            if (strcasecmp($header_field_parts['name'], 'location') === 0) {

                throw new Exception("Bad response.");
            }
        }

    },
];

$url_str = 'http://localhost/bin/HTTP/location-302.php';
$url = new Url($url_str);

$client = new HttpClient();

$response = $client->get($url, $options);

include 'components/response-contents.php';
