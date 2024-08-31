<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

$unique_title = new \LWP\Common\String\UniqueTitle(max: 2, separator: ' ');

echo($unique_title->add('file 2') . PHP_EOL);
echo($unique_title->add('file') . PHP_EOL);
echo($unique_title->add('file') . PHP_EOL);

$unique_title->debug();
