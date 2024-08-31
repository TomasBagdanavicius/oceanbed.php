<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Constraints\CharsetConstraint;

$charset_constraint = new CharsetConstraint('ascii');
$validator = $charset_constraint->getValidator();
$validation_result = $validator->validate("Hello World!");

Demo\assert_true($validation_result, "Incorrect validation result");
