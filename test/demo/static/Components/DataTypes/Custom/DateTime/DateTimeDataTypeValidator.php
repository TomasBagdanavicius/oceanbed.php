<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\DateTime\DateTimeDataTypeValidator;

$validator = new DateTimeDataTypeValidator('2022-01-01 08:00:00');

print "Result: ";
var_dump($validator->validate());
