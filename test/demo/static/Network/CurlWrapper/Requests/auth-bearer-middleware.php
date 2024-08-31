<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\Url;
use LWP\Network\CurlWrapper\Client as HttpClient;
use LWP\Network\CurlWrapper\Request;
use LWP\Network\Http\Auth\Bearer;
use LWP\Network\Http\HttpMethodEnum;
use LWP\Network\Http\Response;

$my_options = [
    'auth' => [
        'type' => 'Bearer',
        'params' => [
            'token' => 'passwd',
        ],
    ],
];
$options = [
    'follow_location' => false,
];
$url_str = 'https://httpbin.org/bearer';
$url = new Url($url_str);
$client = new HttpClient();
$request_params = [
    HttpMethodEnum::GET,
    $url,
    $options
];
$request_reflection = new ReflectionClass(Request::class);
$redirects_count = 0;

do {

    if ($redirects_count === 10) {
        throw new \Exception("Exceeded number of 10 redirects.");
    }

    $request = $request_reflection->newInstanceArgs($request_params);

    $response = $client->send($request);

    var_dump($response->getBody());

    $requirements = $response->getRequirements();

    $custom_options = [];

    if ($requirements === Response::REQUIREMENT_AUTH_TOKEN) {

        $auth_type = $response->getAuthenticationType();

        if ($auth_type === Bearer::SCHEME_NAME) {

            $custom_options = $my_options;
        }
    }

    $redirects_count++;

} while ($request_params = $response->getNextRequestParams($custom_options));
