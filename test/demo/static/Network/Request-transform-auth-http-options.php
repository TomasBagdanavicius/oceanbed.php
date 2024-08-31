<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(Demo\SRC_PATH . '/../var/config.php');

use LWP\Network\Uri\Url;
use LWP\Network\Request;
use LWP\Network\Http\HttpMethodEnum;

// Endpoint info - HTTP request method and URL.
$http_method = HttpMethodEnum::GET;
$url = new Url('https://api.twitter.com/1.1/account/verify_credentials.json');

$options = [
    'auth' => [
        'type' => 'OAuth1',
        'params' => [
            'consumer_key' => $user_config['social']['x']['consumer_key'],
            'consumer_secret' => $user_config['social']['x']['consumer_key'],
            'oauth_token' => $user_config['social']['x']['oauth_token'],
            'oauth_token_secret' => $user_config['social']['x']['oauth_token_secret'],
        ],
    ],
    'query_params' => [
        'oauth_timestamp' => '1234567890',
        'foo' => 'bar',
    ],
];

// Transforms "$options" by reference.
Request::transformAuthHttpOptions($http_method, $url, $options);

print_r($options);
