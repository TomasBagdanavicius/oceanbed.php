<?php

declare(strict_types=1);

require_once(__DIR__ . '/../src/Autoload.php');
include(__DIR__ . '/../var/config.php');

use LWP\Database\Server as SqlServer;

$sql_server = new SqlServer(
    hostname: $user_config['database']['host'],
    username: $user_config['database']['username'],
    password: $user_config['database']['password']
);

$database = $sql_server->getDatabase($user_config['database']['main_database']);
$db_link = $database->server_link;
