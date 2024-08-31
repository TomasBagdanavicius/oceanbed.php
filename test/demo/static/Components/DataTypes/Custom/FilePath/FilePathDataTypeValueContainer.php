<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\FilePath\FilePathDataTypeValueContainer;

$value_container = new FilePathDataTypeValueContainer(__DIR__);

echo "Class: ";
var_dump($value_container::class);

echo "Value: ";
var_dump($value_container->getValue());
