<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\String\Str;

Demo\assert_true(
    Str::insertAt("Hello !", 6, "World") === "Hello World!",
    "Unexpected result"
);
