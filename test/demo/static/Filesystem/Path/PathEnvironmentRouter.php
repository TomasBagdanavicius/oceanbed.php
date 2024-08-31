<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Path\PathEnvironmentRouter;

$path = PathEnvironmentRouter::getStaticInstance();
var_dump($path::class);

$file_path = $path::getFilePathInstance($_SERVER['DOCUMENT_ROOT']);
var_dump($file_path::class);
var_dump($file_path->__toString());
