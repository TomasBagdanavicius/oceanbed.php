<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Constraints\MaxSizeConstraint;
use LWP\Components\Constraints\MinSizeConstraint;

$max_size_constraint = new MaxSizeConstraint(10);
$min_size_constraint = new MinSizeConstraint(11);

try {
    $max_size_constraint->collisionAssistance($min_size_constraint);
    $result = false;
} catch (\RangeException) {
    $result = true;
}

Demo\assert_true($result, "Failed to detect collision");
