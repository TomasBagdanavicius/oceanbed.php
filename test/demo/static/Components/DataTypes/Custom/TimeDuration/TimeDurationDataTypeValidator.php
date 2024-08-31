<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\TimeDuration\TimeDurationDataTypeValidator;

$validator = new TimeDurationDataTypeValidator('P1WT4H');

print "Result for \"{$validator->value}\": ";
var_dump($validator->validate());

$validator = new TimeDurationDataTypeValidator('PT1W');

print "Result for \"{$validator->value}\": ";
var_dump($validator->validate());
