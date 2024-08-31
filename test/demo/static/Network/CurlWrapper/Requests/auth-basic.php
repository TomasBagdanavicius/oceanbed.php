<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\Url;
use LWP\Network\CurlWrapper\Client as HttpClient;
use LWP\Network\CurlWrapper\ResponseBuffer;
use LWP\Network\Http\Auth\Basic;

$options = [
    'auth' => [
        'type' => Basic::SCHEME_NAME,
        'params' => [
            'username' => 'user',
            'password' => 'passwd',
        ],
    ],
];

$url_str = 'https://httpbin.org/basic-auth/user/passwd';

$url = new Url($url_str);

$client = new HttpClient();

$response = $client->get($url, $options);

include 'components/response-contents.php';
