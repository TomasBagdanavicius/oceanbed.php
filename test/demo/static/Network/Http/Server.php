<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Server;

echo "Address: ";
var_dump(Server::getAddress());

echo "Start line: ";
var_dump(Server::getStartLine());

echo "Request headers: ";
var_dump(Server::getRequestHeaders()->toArray());
