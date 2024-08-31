<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(Demo\SRC_PATH . '/../var/config.php');

use LWP\Network\Uri\Url;
use LWP\Network\Http\Auth\OAuth\OAuth2\OAuth2;
use LWP\Network\Http\Server;
use LWP\Network\Uri\UriReference;
use LWP\Network\CurlWrapper\Client as HTTP_Client;
use LWP\Network\Http\HttpMethodEnum;
use LWP\Network\Http\Auth\Bearer;

// Options for this script.
// Whether to allow this script to perform redirects. Otherwise it will print URLs that need to follow.
$allow_redirects = false;

// Authentication information.

$consumer_key = $user_config['social']['x']['consumer_key'];
$consumer_secret = $user_config['social']['x']['consumer_secret'];
$screen_name = $user_config['social']['x']['screen_name'];

// Service provider data
$token_endpoint = new Url('https://api.twitter.com/oauth2/token');

// Endpoints.
$verify_credentials_url = new Url('https://api.twitter.com/1.1/account/verify_credentials.json');
$user_timeline_url = new Url('https://api.twitter.com/1.1/statuses/user_timeline.json');

// Twitter translates consumer key and consumer secret into client id and client secret.
$oauth2 = new OAuth2($consumer_key, $consumer_secret);

// Step 1: obtains the Bearer access token.
if (!isset($_GET['access_token'])) {

    $response = $oauth2->getAccessToken($token_endpoint, OAuth2::GRANT_CLIENTCREDENTIALS, [], true);

    // Builds current URL with some extra query parameters.
    $next_location_url = Server::getCurrentUrl();
    $query_component = $next_location_url->getQueryComponent();
    $query_component->set('access_token', $response->access_token);
    $query_component->set('token_type', $response->token_type);

    if (!$allow_redirects) {
        print("Please go to: " . $next_location_url);
    } else {
        header("Location: " . $next_location_url);
    }

    // Step 2: authenticates API requests with the Bearer Token.
} else {

    $oauth2->setAccessToken($_GET['access_token']);

    $data = [
        /* Required. */
        'screen_name' => $screen_name,
        /* Compulsory. */
        'count' => 1,
    ];

    $response = $oauth2->request($user_timeline_url, Bearer::SCHEME_NAME, $data, HttpMethodEnum::GET);

    print_r($response->getBody());
}
