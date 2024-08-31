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

$client_id = $user_config['social']['google']['client_id'];
$client_secret = $user_config['social']['google']['client_secret'];
$redirection_endpoint = $user_config['oauth_callback_url'] . '?service_provider=Google&oauth_version=2';
$token_endpoint = 'https://accounts.google.com/o/oauth2/token';

$url_str = 'https://slides.googleapis.com/v1/presentations/1Z4H0zRxlVB5ZtFUqzvb8tkbZv37oAVm_GrnroWY4dsM';
// This will eventually expire. Need either up to date "access_token" in query params or value update.
$temp_access_token = $user_config['social']['google']['temp_access_token'];

$options = [
    'auth' => [
        'type' => 'OAuth2',
        'params' => [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'token_type' => 'Bearer',
            'access_token' => ($_GET['access_token'] ?? $temp_access_token),
        ],
    ],
];

$url = new Url($url_str);

$client = new HttpClient();

$response = $client->get($url, $options);

include 'components/response-contents.php';
