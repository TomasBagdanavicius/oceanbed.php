<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

use LWP\Database\Table;

$table = $database->getTable('static');

Demo\assert_true(
    $table->hasColumn('title', force: true),
    "Unexpected result"
);
