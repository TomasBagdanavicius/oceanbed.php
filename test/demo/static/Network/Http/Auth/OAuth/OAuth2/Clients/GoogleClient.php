<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(Demo\SRC_PATH . '/../var/config.php');

use LWP\Network\Http\Auth\OAuth\OAuth2\Clients\GoogleClient;
use LWP\Network\Uri\Url;

$client_id = $user_config['social']['google']['client_id'];
$client_secret = $user_config['social']['google']['client_secret'];
$redirect_uri = new Url($user_config['project_url'] . '/test/demo/static/Network/Http/Auth/OAuth2_google.php', Url::HOST_VALIDATE_NONE);

$google_client = new GoogleClient($client_id, $client_secret);
$google_client->setRedirectUri($redirect_uri);
$google_client->setScope("openid email profile");

echo "Auth URL: ";
prl($google_client->createAuthUrl()->__toString());
