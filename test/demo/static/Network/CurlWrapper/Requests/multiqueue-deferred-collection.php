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
use LWP\Network\Http\ResponseBuffer;
use LWP\Components\Messages\Message;

$options = [

];

$url = new Url('https://www.lwis.net/bin/Text/en-pangram.txt');
$url_2 = new Url('https://www.g9i3mn49ie32ol5m4nbd2.qklpx');

$client = new HttpClient();

$promise = $client->getDeferred($url, 'lwis_bin', $options);
$promise_2 = $client->getDeferred($url_2, 'invalid', $options);

/* Promise 1 */

$promise->addCallbacks(function (Response $response) {

    include 'components/response-contents.php';

    echo PHP_EOL;

}, function (\Throwable|Message $message) {

    print "Promise exception: ";

    if ($message instanceof \Throwable) {
        print $message->getMessage();
    } else {
        print $message->text;
    }

    echo PHP_EOL;

});

/* Promise 2 */

$promise_2->addCallbacks(function (Response $response) {

    include 'components/response-contents.php';

    echo PHP_EOL;

}, function (\Throwable|Message $message) {

    print "Promise exception: ";

    if ($message instanceof \Throwable) {
        print $message->getMessage();
    } else {
        print $message->text;
    }

    echo PHP_EOL;

});

$promise->process();
