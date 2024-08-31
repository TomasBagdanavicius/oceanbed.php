<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Path\Path;

$basename_components = Path::parseBasename('..');

Demo\assert_true(
    ($basename_components['filename'] === '..' && $basename_components['extension'] === ''),
    "Incorrect basename components"
);
