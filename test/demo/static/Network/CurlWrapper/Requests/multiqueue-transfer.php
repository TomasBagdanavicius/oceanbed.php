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
use LWP\Network\CurlWrapper\ResponseBuffer;

try {

    $options = [

    ];

    $url_1 = new Url('https://www.lwis.net/');
    $url_2 = new Url('https://www.lwis.net/bin/Text/paragraphs.txt');

    $client = new HttpClient($options);

    $queue_id_1 = $client->queue(HttpMethodEnum::GET, $url_1, 'lwis');
    $queue_id_2 = $client->queue(HttpMethodEnum::GET, $url_2, 'lwis_bin');

    $responses_collection = $client->transferAll();

    foreach ($responses_collection as $name => $response) {

        include 'components/response-contents.php';

        echo PHP_EOL . PHP_EOL;
    }

} catch (Throwable $exception) {

    die("Tester exception: " . $exception->getMessage());
}
