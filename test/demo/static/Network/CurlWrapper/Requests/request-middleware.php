<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\Url;
use LWP\Network\Http\HttpMethodEnum;
use LWP\Network\CurlWrapper\Client as HttpClient;
use LWP\Network\CurlWrapper\Request;

$options = [
    // Tells the client not to follow locations automatically.
    'follow_location' => false,
];

$url = new Url('http://localhost/bin/HTTP/location-302-relative.php');

$client = new HttpClient();

$request_params = [
    HttpMethodEnum::GET,
    $url,
    $options,
];

$request_reflection = new ReflectionClass(Request::class);

$redirects_count = 0;

do {

    if ($redirects_count === 10) {
        throw new Exception("Exceeded number of 10 redirects.");
    }

    $request = $request_reflection->newInstanceArgs($request_params);

    $response = $client->send($request);

    /* We can manager each response here. */

    print_r($response->getResponseHeaders()->getStatusLine()->toArray());

    $redirects_count++;

} while ($request_params = $response->getNextRequestParams());
