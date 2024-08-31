<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\Url;
use LWP\Network\CurlWrapper\Request;
use LWP\Network\CurlWrapper\ResponseBuffer;
use LWP\Network\Http\HttpMethodEnum;

/* First of, setup a request */

$url_str = 'https://www.lwis.net/bin/Text/en-pangram.txt';

$url = new Url($url_str);

$options = [
    'headers' => [
        'X-Custom' => 'custom-header-value',
    ],
    'throw_errors' => false,
];

$request = new Request(HttpMethodEnum::GET, $url, $options);

$request->setCurlOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2);


/* Now setup a response buffer. */

$response = new ResponseBuffer($request);

// Status line.
$response->startResponseHeaders("HTTP/1.1 404 Not Found");
// Headers.
$response->getResponseHeaders()->set("Location", "http://localhost/");
$response->getResponseHeaders()->set("Content-Length", "1000");
// Body.
$response->setBody("The quick brown fox jumps over the lazy dog.");
// Messages.
$response->issueError("There has been an error.", 1);


/* Read from the response buffer */

print_r($response->getResponseHeaders()->getStatusLine()->toArray());
print_r($response->getResponseHeaders()->toArray());
print $response->getBody() . PHP_EOL;

$messages = $response->getMessages();

if ($messages->count()) {

    foreach ($messages as $message) {

        print $message->text . PHP_EOL;
    }

}
