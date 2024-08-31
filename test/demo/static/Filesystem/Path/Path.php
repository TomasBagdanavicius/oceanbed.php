<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Path\Path;
use LWP\Filesystem\Path\PathEnvironmentRouter;

$pathname = '/one/../two\\/three/.';
$path = PathEnvironmentRouter::getStaticInstance();

var_dump($path::normalize($pathname));
#var_dump( $path::splitAtRoot($pathname)['root'] );
