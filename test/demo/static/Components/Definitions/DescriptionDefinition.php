<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Definitions\DescriptionDefinition;

$comment_definition = new DescriptionDefinition("This is my comment.");

var_dump($comment_definition->getIndexableData());
var_dump($comment_definition->getArray());

$comment_definition->setValue("This is my new comment.");
var_dump($comment_definition->getValue());
