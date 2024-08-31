<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Constraints\CharsetConstraint;

// The goal is to check that `TypeError` is not thrown
$charset_constraint = new CharsetConstraint('ascii');

Demo\assert_true(
    $charset_constraint instanceof CharsetConstraint,
    "Character constraint object is not valid"
);
