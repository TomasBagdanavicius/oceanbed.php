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
use LWP\Network\Http\ResponseBuffer;

$options = [
    'on_headers' => function (ResponseBuffer $response_buffer, int $header_lines_count, int $redirections_count) {

        if ($response_buffer->getResponseHeaders()->containsKey('x-special-header-field')) {

            throw new Exception("Bad response.");
        }

    },
];

$url_1 = new Url('https://www.lwis.net/');
$url_2 = new Url('https://www.lwis.net/bin/HTTP/special-header-field.php');

$client = new HttpClient();

$client->queue(HttpMethodEnum::GET, $url_1, 'lwis', $options);
$client->queue(HttpMethodEnum::GET, $url_2, 'lwis_bin', $options);

$responses_collection = $client->transferAll();

/* Filters out aborted requests only */

print "Contains failed requests: ";
var_dump($responses_collection->hasAborted());

$aborted_responses = $responses_collection->getAborted();
print "Failed requests count: ";
var_dump($aborted_responses->count());

foreach ($aborted_responses as $name => $response) {

    $messages = $response->getMessages();

    foreach ($messages as $message) {

        print $message->text . PHP_EOL;
    }
}

/* Filters out a specific response by name */

$lwis_response = $responses_collection->get('lwis');

var_dump($lwis_response->getState() === ResponseBuffer::STATE_COMPLETED);

// Filters out completed requests

$completed_responses = $responses_collection->getCompleted();

foreach ($completed_responses as $name => $response) {

    print "Response \"" . $name . "\" has been completed." . PHP_EOL;
}
