<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\DateTimeFormat;

$result = DateTimeFormat::parseFormat('Y-m-d {T}H:i:s {custom text %here%}', return_as_array: true);

Demo\assert_true(
    $result === [
        'Y-m-d ',
        '{T}',
        'H:i:s ',
        '{custom text %here%}',
    ],
    "Unexpected result"
);
