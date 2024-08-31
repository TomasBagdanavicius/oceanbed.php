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
use LWP\Network\Uri\SearchParams;

// Options for this script.
// Whether to allow this script to perform redirects. Otherwise it will print URLs that need to follow.
$allow_redirects = false;

// Authorization information
$client_id = $user_config['social']['google']['client_id'];
$client_secret = $user_config['social']['google']['client_secret'];
$redirection_endpoint = new Url($user_config['oauth_callback_url'] . '?service_provider=Google&oauth_version=2');

// Service provider data
$authorization_endpoint = new Url('https://accounts.google.com/o/oauth2/auth');
$token_endpoint = new Url('https://accounts.google.com/o/oauth2/token');

// Endpoints.
$slides_presentations_url = new Url('https://slides.googleapis.com/v1/presentations/1Z4H0zRxlVB5ZtFUqzvb8tkbZv37oAVm_GrnroWY4dsM');

$oauth2 = new OAuth2($client_id, $client_secret);

// Step 1: acquire the code.
if (!isset($_GET['code']) && !isset($_GET['access_token']) && !isset($_GET['refresh_token'])) {

    $authorization_url = $oauth2->getAuthUrl($authorization_endpoint, $redirection_endpoint, [
        'access_type' => 'offline',
        'scope' => 'https://www.googleapis.com/auth/presentations.readonly', // See: https://developers.google.com/identity/protocols/googlescopes
        'include_granted_scopes' => 'true',
        'state' => 'state_parameter_passthrough_value',
        'prompt' => 'consent', // Prompt the user for consent.
    ]);

    if (!$allow_redirects) {
        print("Please go to: " . $authorization_url);
    } else {
        header("Location: " . $authorization_url);
    }

    // Step 2: exchange the code with an access token.
} elseif (isset($_GET['code']) && !isset($_GET['access_token'])) {

    $params = [
        'code' => $_GET['code'],
        'redirect_uri' => $redirection_endpoint->__toString(),
    ];

    $response = $oauth2->getAccessToken($token_endpoint, OAuth2::GRANT_AUTHORIZATIONCODE, $params);

    // Builds current URL with some extra query parameters.
    $next_location_url = Server::getCurrentUrl();
    $query_component = $next_location_url->getQueryComponent();
    $query_component->set('access_token', $response->access_token);
    $query_component->set('refresh_token', $response->refresh_token);

    if (!$allow_redirects) {
        print("Please go to: " . $next_location_url);
    } else {
        header("Location: " . $next_location_url);
    }

    // Step 3: fetch data by using the access token.
} elseif (isset($_GET['access_token'])) {

    $oauth2->setAccessToken($_GET['access_token']);

    $response = $oauth2->request($slides_presentations_url, OAuth2::TOKEN_BEARER);

    // Builds current URL with some extra query parameters.
    $refresh_token_location_url = Server::getCurrentUrl();
    $query_component = new SearchParams();
    $query_component->set('refresh_token', $_GET['refresh_token']);
    $refresh_token_location_url->setQueryComponent($query_component);

    print "Access Token: " . $_GET['access_token'] . PHP_EOL . PHP_EOL;
    print "Refresh Token: " . $_GET['refresh_token'] . PHP_EOL . PHP_EOL;
    print "To get a new access token, please go to: " . $refresh_token_location_url . PHP_EOL;
    print PHP_EOL;

    print_r($response->getBody());

    // Step X: refresh access token.
} elseif (isset($_GET['refresh_token'])) {

    $params = [
        'refresh_token' => $_GET['refresh_token'],
        'redirect_uri' => $redirection_endpoint->__toString(),
    ];

    $response = $oauth2->getAccessToken($token_endpoint, OAuth2::GRANT_REFRESHTOKEN, $params);

    print_r($response);

} else {

    die("Invalid response.");
}
