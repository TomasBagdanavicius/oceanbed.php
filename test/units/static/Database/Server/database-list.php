<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

$list = $sql_server->getDatabaseList();

Demo\assert_true(
    // For now, just checking if it's an array, because it's more difficult to get a full list
    is_array($list),
    "Database list is not an array"
);
