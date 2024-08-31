<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Constraints\CharsetConstraint;

$character_set = 'utf-8';

try {
    $charset_constraint = new CharsetConstraint($character_set);
    $result = false;
} catch (\ValueError) {
    $result = true;
}

Demo\assert_true(
    $result,
    sprintf(
        "Character set \"%s\" should not be recognizable",
        $character_set
    )
);
