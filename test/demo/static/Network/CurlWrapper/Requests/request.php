<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\Url;
use LWP\Network\Http\HttpMethodEnum;
use LWP\Network\CurlWrapper\Client as HttpClient;
use LWP\Network\CurlWrapper\Request;

$options = [

];

$url = new Url('http://localhost/toolkit/request.php');

$request = new Request(HttpMethodEnum::GET, $url, $options);

$client = new HttpClient($options);

$response = $client->send($request);

include 'components/response-contents.php';
