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

$files_to_upload = [
    'file1' => new \CURLFile(($_SERVER['DOCUMENT_ROOT'] . '/storage/sample-files/images/500x300_Sunflower.jpg'), 'image/jpeg'),
    'file2' => new \CURLFile(($_SERVER['DOCUMENT_ROOT'] . '/storage/sample-files/images/500x300_Earth.jpg'), 'image/jpeg'),
    'file3' => new \CURLStringFile("File contents.", 'test.txt', 'text/plain'),
];

$options = [

];

$url = new Url('http://localhost/toolkit/upload.php');

$client = new HttpClient();

$response = $client->upload($url, $files_to_upload, $options);
$response_state = $response->getState();

include 'components/response-contents.php';
