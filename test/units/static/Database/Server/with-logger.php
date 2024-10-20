<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/units/shared/utilities.php');

use LWP\Common\FileLogger;
use LWP\Database\Server as SqlServer;

[$temp_file_pathname, $temp_file_handle] = createTempFileInFilesystemBin();
$sql_server = new SqlServer(
    hostname: $user_config['database']['host'],
    username: $user_config['database']['username'],
    password: $user_config['database']['password'],
    file_logger: new FileLogger($temp_file_pathname)
);
$database = $sql_server->getDatabase($user_config['database']['test_database']);
$db_link = $database->server_link;

$temp_file_contents = file_get_contents($temp_file_pathname);

fclose($temp_file_handle);
$unlink_temp_file = unlink($temp_file_pathname);

if (!$unlink_temp_file) {
    throw new \RuntimeException("Could not delete temporary file for logging");
}

Demo\assert_true(
    $temp_file_contents !== "",
    "Session ID is not an integer"
);
