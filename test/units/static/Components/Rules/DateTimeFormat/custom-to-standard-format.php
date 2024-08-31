<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\DateTimeFormat;

$result = DateTimeFormat::customFormatToStandardFormat('Y-m-d {T}H:i:s {GMT}');

Demo\assert_true(
    $result === 'Y-m-d \TH:i:s \G\M\T',
    "Unexpected result"
);
