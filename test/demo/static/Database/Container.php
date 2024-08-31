<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

use LWP\Components\Datasets\Container;

$dataset = $database->getTable('static');

$container = new Container('title', $dataset);
pr($container->getSchema());
#var_dump($container->getDefinitionCollection());
#pr($container->getIndexablePropertyList());
#pr($container->getIndexableData());
#var_dump($container->getIndexablePropertyValue('type'));
