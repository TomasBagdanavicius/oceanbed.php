<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Message\StatusLine;

$status_line_str = 'HTTP/2.0 401 Unauthorized';

$status_line = StatusLine::fromString($status_line_str);

var_dump($status_line->getStatusCode());
var_dump($status_line->getReasonPhrase());
