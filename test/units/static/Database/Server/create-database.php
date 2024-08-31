<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

use LWP\Database\Database;

$i = 1;
do {
    $database_name = 'database' . $i;
    $result = $db_link->execute_query("SELECT `SCHEMA_NAME` FROM `INFORMATION_SCHEMA`.SCHEMATA WHERE `SCHEMA_NAME` = ? LIMIT 1", [$database_name]);
} while ($result->num_rows);

$database = $sql_server->createDatabase($database_name);

$result = $db_link->execute_query("SELECT `SCHEMA_NAME` FROM `INFORMATION_SCHEMA`.SCHEMATA WHERE `SCHEMA_NAME` = ? LIMIT 1", [$database_name]);

if (!$result->num_rows) {
    throw new \RuntimeException(sprintf(
        "Database \"%s\" was not found",
        $database_name
    ));
}

$db_link->query(sprintf("DROP DATABASE `%s`", $database_name));

Demo\assert_true(
    $database instanceof Database,
    "Database was not properly created"
);
