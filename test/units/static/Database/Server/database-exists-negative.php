<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');
require_once(Demo\TEST_PATH . '/units/shared/utilities.php');

$unexisting_database_name = generateUniqueDatabaseName($db_link);

Demo\assert_true(
    $sql_server->databaseExists($unexisting_database_name) === false,
    "Database was found"
);
