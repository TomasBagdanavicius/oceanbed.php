<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\String\Str;

/* Comparison */
var_dump(Str::accentInsensitiveCompare("Ąžuolas", "azuolas"));
var_dump(Str::accentInsensitiveCompare("azuolas", "Ąžuolas"));

var_dump(Str::isAscii("abč"));

var_dump(Str::mbStringWrap("ąbč.txt", [0], 3, '<mark>', '</mark>'));
