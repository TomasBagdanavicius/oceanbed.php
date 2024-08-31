<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

use LWP\Components\Datasets\Container;
use LWP\Components\Datasets\ContainerCollection;
use LWP\Common\Conditions\Condition;

$dataset = $database->getTable('static');

$container1 = new Container('title', $dataset);
$container2 = new Container('name', $dataset);
$container_collection = new ContainerCollection();
$container_collection->add($container1);
$container_collection->add($container2);
var_dump(count($container_collection));

$condition = new Condition('dataset_name', 'static');
$filtered_collection = $container_collection->matchCondition($condition);
var_dump(count($filtered_collection));

$filtered_collection = $container_collection->matchBySingleCondition('container_name', 'title');
var_dump(count($filtered_collection));
