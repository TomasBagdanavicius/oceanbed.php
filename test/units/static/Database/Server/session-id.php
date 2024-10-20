<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

$session_id = $sql_server->getSessionId();

Demo\assert_true(
    // For now, just checking if it's an integer, because it's more difficult to get the exact number
    is_int($session_id),
    "Session ID is not an integer"
);
