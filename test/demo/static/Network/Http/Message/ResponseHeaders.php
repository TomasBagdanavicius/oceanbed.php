<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Message\StatusLine;
use LWP\Network\Http\Message\ResponseHeaders;

$status_line = StatusLine::fromString('HTTP/1.1 401 Unauthorized');

$response_headers = new ResponseHeaders($status_line, [
    'date' => date('Y-m-d\TH:i:s'),
]);

$response_headers->set('server', 'Apache');
$response_headers->set('x-powered-by', 'PHP');
$response_headers->set('location', '/dir/index.html');

print $response_headers;

var_dump($response_headers->hasNextLocation());
