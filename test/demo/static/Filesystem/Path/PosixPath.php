<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Path\Path;
use LWP\Filesystem\Path\PosixPath;

#var_dump( PosixPath::isAbsolute('/one/two') );

#print_r( PosixPath::parse('/one/two/three/four') );

#var_dump( PosixPath::normalize('/../one/incorrect/../two/three/./four/.') );

#var_dump( PosixPath::resolve('/zero', '/one/two/odd', '../three/./four') );

#var_dump( PosixPath::join('/zero', '/one/two/odd', '../three/./four') );

#var_dump( PosixPath::relative('/one/two/../three/four', '/one/three/five/six/seven') );
