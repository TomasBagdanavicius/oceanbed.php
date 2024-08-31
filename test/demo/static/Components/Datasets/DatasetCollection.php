<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

use LWP\Components\Datasets\DatasetCollection;

$table1 = $database->getTable('temp');
$table2 = $database->getTable('test');

$dataset_collection = new DatasetCollection();
$dataset_collection->add($table1);
$dataset_collection->add($table2);

var_dump($dataset_collection->count());
