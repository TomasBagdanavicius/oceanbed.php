<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Database\QueryCollection;

$query_collection = new QueryCollection();

$query_collection->add("SELECT * FROM `table_A`");
$query_collection->add("DELETE FROM `table_B` WHERE `id` = '1'");

print $query_collection;
