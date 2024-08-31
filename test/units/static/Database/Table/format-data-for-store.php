<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

$table = $database->getTable('data_types');

$data_to_store = [
    'my_datetime' => '31st December 2021 4.30 p.m.',
    'my_decimal' => '1 234 567,89',
    'my_int' => true,
    'my_unique_int' => false,
    'my_boolean' => false,
];

$data_store_formatter_iterator = $table->containers->getStoreFieldValueFormatterIterator($data_to_store);

foreach ($data_store_formatter_iterator as $container_name => $value) {
    $data_to_store[$container_name] = $value;
}

Demo\assert_true(
    ($data_to_store === [
        'my_datetime' => '2021-12-31 16:30:00',
        'my_decimal' => '1234567.89',
        'my_int' => 1,
        'my_unique_int' => 0,
        'my_boolean' => 0,
    ]),
    "Formatted data does not match expected output"
);
