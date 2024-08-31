<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\String\Str;

$positions = Str::accentInsensitivePosAll("Ąžuolas ąžuolėlis, gėlės vazonuose", "az", case_sensitive: false);

Demo\assert_true(
    $positions === [0, 8, 26],
    "Unexpected result"
);
