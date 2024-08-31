<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Message\StartLine;
use LWP\Network\Http\Message\RequestHeaders;

$start_line = StartLine::fromString('GET /dir/index.html HTTP/1.1');

$request_headers = new RequestHeaders($start_line, [
    'date' => date('Y-m-d\TH:i:s'),
]);

$request_headers->set('host', 'domain.com');
$request_headers->set('connection', 'keep-alive');

echo $request_headers;
