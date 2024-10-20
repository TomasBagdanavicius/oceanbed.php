<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

$candidate_database_names = ['test'];
$existing_database_name = null;

foreach ($candidate_database_names as $database_name) {
    $result = $db_link->execute_query("SELECT `SCHEMA_NAME` FROM `INFORMATION_SCHEMA`.SCHEMATA WHERE `SCHEMA_NAME` = ? LIMIT 1", [$database_name]);
    if ($result->num_rows !== 0) {
        $existing_database_name = $database_name;
        break;
    }
}

if ($existing_database_name === null) {
    throw new \RuntimeException("None of the specified databases were found");
}

Demo\assert_true(
    $sql_server->databaseExists($existing_database_name),
    "Database was not found"
);
