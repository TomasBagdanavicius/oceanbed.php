<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\Url;
use LWP\Network\Uri\UrlReference;
use LWP\Network\CurlWrapper\Client as HttpClient;
use LWP\Network\Http\Response;

$options = [

];

$url = new Url('https://www.lwis.net/bin/Text/en-pangram.txt');

$client = new HttpClient();

$promise = $client->getDeferred($url, 'lwis', $options);

$promise->addCallbacks(function (Response $response) {

    include 'components/response-contents.php';

}, function (Exception $exception) {

    print "Exception: " . $exception->getMessage();

});

$promise->process();
