<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\FileLogger;

$pathname = Demo\TEST_PATH . '/log/test.log';

$file_logger = new FileLogger($pathname);
$file_logger->logText("Testing logger...");
