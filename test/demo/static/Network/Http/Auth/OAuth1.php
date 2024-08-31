<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(Demo\SRC_PATH . '/../var/config.php');

use LWP\Network\Uri\Url;
use LWP\Network\Uri\UriPathComponent;
use LWP\Network\Http\Auth\OAuth\OAuth1;
use LWP\Network\Http\Server;
use LWP\Network\Http\HttpMethodEnum;

// Options for this script.
// Whether to allow this script to perform redirects. Otherwise it will print URLs that need to follow.
$allow_redirects = false;

// Authentication information.
$consumer_key = $user_config['social']['x']['consumer_key'];
$consumer_secret = $user_config['social']['x']['consumer_secret'];
// Query params are not allowed in the callback URL in Twitter app's settings, however it's working here and will be passed through.
$callback_url = new Url($user_config['oauth_callback_url'] . '?oauth_version=1&service_provider=Twitter');

// Service provider data.
$authenticate_url = new Url('https://api.twitter.com/oauth/authenticate');
$request_token_url = new Url('https://api.twitter.com/oauth/request_token');
$access_token_url = new Url('https://api.twitter.com/oauth/access_token');

// Endpoints.
$verify_credentials_url = new Url('https://api.twitter.com/1.1/account/verify_credentials.json');
$user_timeline_url = new Url('https://api.twitter.com/1.1/statuses/user_timeline.json');

// Initialize OAuth1 object instance.
$oauth1 = new OAuth1($consumer_key, $consumer_secret);

// Step 1: Obtain service's (Twitter's) authorization URL.
if (!isset($_GET['oauth_token']) && !isset($_GET['access_token'])) {

    $authorization_url = $oauth1->getAuthUrl($authenticate_url, $request_token_url, $callback_url);

    if (!$allow_redirects) {
        print("Please go to: " . $authorization_url);
    } else {
        header("Location: " . $authorization_url);
    }

    // Step 2: Capture "oauth_token" and "oauth_verifier".
} elseif (isset($_GET['oauth_token'], $_GET['oauth_verifier']) && !isset($_GET['access_token']) && !isset($_GET['access_token_secret'])) {

    $oauth1->setToken($_GET['oauth_token']);
    $access_token = $oauth1->getAccessToken($access_token_url, $_GET['oauth_verifier']);

    // Builds current URL with some extra query parameters.
    $next_location_url = Server::getCurrentUrl();
    $query_component = $next_location_url->getQueryComponent();
    $query_component->set('access_token', $access_token['oauth_token']);
    $query_component->set('access_token_secret', $access_token['oauth_token_secret']);

    if (!$allow_redirects) {
        print("Please go to: " . $next_location_url);
    } else {
        header("Location: " . $next_location_url);
    }

    // Step 3: Utilize received access tokens to fetch data.
} elseif (isset($_GET['access_token'], $_GET['access_token_secret'])) {

    $oauth1->setToken($_GET['access_token'], $_GET['access_token_secret']);

    // Option 1: Verify credentials. Gives profile information.
    #$data = $oauth1->request($verify_credentials_url, [], HttpMethodEnum::GET);

    // Option 2: Get user timeline.
    $data = $oauth1->request($user_timeline_url, ['count' => '1', 'exclude_replies' => 'true'], HttpMethodEnum::GET);

    print_r($data->getBody());

} else {

    die("Invalid request.");
}
