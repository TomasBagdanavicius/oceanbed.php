<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\Url;
use LWP\Network\Http\RequestMessage;
use LWP\Network\CurlWrapper\Client as HttpClient;
use LWP\Network\CurlWrapper\Request;
use LWP\Network\CurlWrapper\ResponseBuffer;

try {

    $options = [
        'on_headers' => function (ResponseBuffer $response_buffer, int $header_lines_count, int $redirections_count) {

            $response_headers = $response_buffer->getResponseHeaders();

            if (!$response_headers->hasNextLocation()) {

                #print_r( $response_headers->toArray() );
            }

        },
        'progress' => function (ResponseBuffer $response_buffer, int $download_size, int $downloaded, int $upload_size, int $uploaded) {

            #var_dump( $download_size );
        },
        'on_status' => function (ResponseBuffer $response_buffer, int $redirections_count) {

            $request = $response_buffer->getRequest();

            // Identifying the request.
            #var_dump( $request->getId() );
            #var_dump( $request->getName() );
        },
    ];

    $file_handle_1 = fopen($_SERVER['DOCUMENT_ROOT'] . '/private/downloads/file.txt', 'w+');
    $file_handle_2 = fopen($_SERVER['DOCUMENT_ROOT'] . '/private/downloads/file.jpeg', 'w+');

    $url_1 = new Url('http://localhost/bin/HTTP/location-attachment.php');
    $url_2 = new Url('https://www.lwis.net/tmp/500x500_Earth.jpg');

    $client = new HttpClient($options);

    $queue_id_1 = $client->queueFile($file_handle_1, $url_1, 'text');
    $queue_id_2 = $client->queueFile($file_handle_2, $url_2, 'image');

    $responses_collection = $client->transferAll();

    foreach ($responses_collection as $name => $response) {

        #include 'components/response-contents.php';

        echo PHP_EOL . PHP_EOL;
    }

    fclose($file_handle_1);
    fclose($file_handle_2);

} catch (Throwable $exception) {

    die("Tester exception: " . $exception->getMessage());
}
