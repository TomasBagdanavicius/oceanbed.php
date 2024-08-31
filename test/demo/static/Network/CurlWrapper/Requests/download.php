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

$options = [
    'progress' => function (ResponseBuffer $response_buffer, int $download_size, int $downloaded, int $upload_size, int $uploaded) {

        if ($download_size) {

            $percentage_complete = ($downloaded * 100 / $download_size);

            if ($percentage_complete === 100) {

                #throw new Exception("Bad response.");
            }
        }
    }
];

$url = new Url('http://localhost/storage/sample-files/images/1000x1000_Earth.jpg');

$client = new HttpClient();

$filename = ($_SERVER['DOCUMENT_ROOT'] . '/private/downloads/image.jpeg');
$file_handle = fopen($filename, 'w+');

if (!$file_handle) {
    throw new Exception(sprintf("Could not create a file \"%s\".", $filename));
}

$response = $client->download($url, $file_handle, $options);
$response_state = $response->getState();

fclose($file_handle);

include 'components/response-contents.php';
