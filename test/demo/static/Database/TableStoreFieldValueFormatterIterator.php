<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

use LWP\Database\TableStoreFieldValueFormatterIterator;

$table = $database->getTable('data_types');
$data_to_write = [
    'my_datetime' => '31st December 2021 4.30 p.m.',
    'my_decimal' => '1 234 567,89',
    'my_int' => true,
    'my_unique_int' => false,
    'my_boolean' => false,
];
$iterable = $table->containers->getStoreFieldValueFormatterIterator($data_to_write);

foreach ($iterable as $container_name => $formatted_value) {
    echo $container_name . ": ";
    var_dump($formatted_value);
}
