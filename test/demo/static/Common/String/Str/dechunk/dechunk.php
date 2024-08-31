<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\String\Str;

$chunked_string = "4\r\nWiki\r\n5\r\npedia\r\ne\r\n in\r\n\r\nchunks.\r\n0\r\n";

var_dump(Str::dechunk($chunked_string));

// Test support for LF line breaks.
$chunked_string = "3\nThe\n7\n quick \n9\nbrown fox\n7\n jumps \n11\nover the lazy dog\n0\n";

var_dump(Str::dechunk($chunked_string));
