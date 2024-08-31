<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Message\StartLine;
use LWP\Network\Http\HttpMethodEnum;

/* Case 1: Regular */

$start_line = new StartLine(HttpMethodEnum::GET, '/dir/index.html', '2.0');

echo $start_line . PHP_EOL;

/* Case 2: Create from string */

$start_line_str = 'GET /dir/index.html HTTP/2.0';

$start_line = StartLine::fromString($start_line_str);

var_dump($start_line->method);
var_dump($start_line->getRequestTarget());
var_dump($start_line->getProtocolVersion());
