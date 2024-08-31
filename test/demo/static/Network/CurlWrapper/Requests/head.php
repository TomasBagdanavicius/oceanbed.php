<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\Url;
use LWP\Network\CurlWrapper\Client as HttpClient;
use LWP\Network\Http\ResponseBuffer;

$options = [

];

$url = new Url('https://www.lwis.net/');

$client = new HttpClient();

$response = $client->head($url, $options);
$response_state = $response->getState();

if ($response_state == ResponseBuffer::STATE_COMPLETED) {

    print_r($response->getResponseHeaders()->getStatusLine()->toArray());
    print_r($response->getResponseHeaders()->toArray());

} elseif ($response_state == ResponseBuffer::STATE_ABORTED) {

    $messages = $response->getMessages();

    foreach ($messages as $message) {

        print $message->text . PHP_EOL;
    }

}
