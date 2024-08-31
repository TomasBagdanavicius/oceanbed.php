<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Path\WindowsPath;

#var_dump( WindowsPath::isAbsolute('C:\\one\\two\\three') );

#var_dump( WindowsPath::splitAtRoot('C:\\one\\two\\three') );

#print_r( WindowsPath::parse('C:\\one\\two\\three') );

#var_dump( WindowsPath::normalize('C:\\..\\one\\two\\odd\\..\\three') );

#var_dump( WindowsPath::normalize('\\\\Server\\\\Share\\..\\foo\\bar\\.') );

#var_dump( WindowsPath::normalize('C:/Users/John/Documents') );

#var_dump( WindowsPath::isUNC('\\\\Server\\Share\\foo\\bar') );
