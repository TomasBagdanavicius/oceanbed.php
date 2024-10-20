<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

use LWP\Database\Server as SqlServer;

echo "Class name: ";
var_dump($sql_server::class);

echo "Database exists: ";
var_dump($sql_server->databaseExists('test'));

echo "Database list: ";
print_r($sql_server->getDatabaseList());

echo "Format quoted identifier: ";
var_dump(SqlServer::formatAsQuotedIdentifier('he.llo', first_dot_as_abbr_mark: true));
