<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

$table = $database->getTable('data_types');
$data_to_update = [
    'my_datetime' => date('jS F Y g.i a', time()), // Requires adjustment
    'my_decimal' => '1 234 567,89', // Requires adjustment
    'my_unique_varchar' => 'B', // Requires product
];

var_dump($table->update('id', 1, $data_to_update, product: true));
