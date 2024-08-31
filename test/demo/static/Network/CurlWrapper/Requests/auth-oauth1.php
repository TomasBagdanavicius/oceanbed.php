<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(Demo\SRC_PATH . '/../var/config.php');

use LWP\Network\Uri\Url;
use LWP\Network\CurlWrapper\Client as HttpClient;
use LWP\Network\CurlWrapper\Response;

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
        // Works with "user_timeline".
        'count' => '3',
    ],
];

$url = new Url('https://api.twitter.com/1.1/account/verify_credentials.json');
#$url = new Url('https://api.twitter.com/1.1/statuses/user_timeline.json');

$client = new HttpClient();

// If no auth options are provided, Twitter comes back with 400 Bad Request.
$response = $client->get($url, $options);

include 'components/response-contents.php';
