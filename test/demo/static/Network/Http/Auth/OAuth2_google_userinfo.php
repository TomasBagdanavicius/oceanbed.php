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
use LWP\Network\Uri\SearchParams;

// Options for this script.
// Whether to allow this script to perform redirects. Otherwise it will print URLs that need to follow.
$allow_redirects = false;
$current_url = Server::getCurrentUrl();

// Authentication information.

$client_id = $user_config['social']['google']['client_id'];
$client_secret = $user_config['social']['google']['client_secret'];
$redirection_endpoint = clone $current_url;
$redirection_endpoint->unsetQueryString();

// Service provider data
$authorization_endpoint = new Url('https://accounts.google.com/o/oauth2/auth');
$token_endpoint = new Url('https://oauth2.googleapis.com/token');

// Endpoints.
$get_userinfo_url = new Url('https://www.googleapis.com/oauth2/v3/userinfo');

$oauth2 = new OAuth2($client_id, $client_secret);

// Step 1:
if (!isset($_GET['code']) && !isset($_GET['access_token']) && !isset($_GET['refresh_token'])) {

    $authorization_url = $oauth2->getAuthUrl($authorization_endpoint, $redirection_endpoint, [
        'access_type' => 'offline',
        // This gives both: name and email
        // Other scopes: https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile
        'scope' => 'openid email profile', // See: https://developers.google.com/identity/protocols/googlescopes
        'include_granted_scopes' => 'true',
        'state' => 'state_parameter_passthrough_value',
        'prompt' => 'consent', // Prompt the user for consent.
    ]);

    if (!$allow_redirects) {
        print("Please go to: " . $authorization_url);
    } else {
        header("Location: " . $authorization_url);
    }

    // Step 2:
} elseif (isset($_GET['code']) && !isset($_GET['access_token'])) {

    $params = [
        'code' => $_GET['code'],
        'redirect_uri' => $redirection_endpoint->__toString(),
    ];

    $response = $oauth2->getAccessToken($token_endpoint, OAuth2::GRANT_AUTHORIZATIONCODE, $params);

    // Builds current URL with some extra query parameters.
    $next_location_url = clone $current_url;
    $query_component = $next_location_url->getQueryComponent();
    $query_component->set('access_token', $response->access_token);
    $query_component->set('refresh_token', $response->refresh_token);

    if (!$allow_redirects) {
        print("Please go to: " . $next_location_url);
    } else {
        header("Location: " . $next_location_url);
    }

} elseif (isset($_GET['access_token'])) {

    $oauth2->setAccessToken($_GET['access_token']);

    $response = $oauth2->request($get_userinfo_url, OAuth2::TOKEN_BEARER);

    // Builds current URL with some extra query parameters.
    $refresh_token_location_url = clone $current_url;
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
