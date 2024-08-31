<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

$table = $database->getTable('data_types');
$data_store_formatter = $table->getStoreFieldValueFormatter();

$data_to_insert = [
    'title' => 'Table Insert Middleware',
    'my_datetime' => '31st December 2021 4.30 p.m.', // Requires adjustment
    #'my_datetime' => '2022-01-01 08:00:00',
    'my_decimal' => '1 234 567,89', // Requires adjustment
    #'my_decimal' => '12345.67',
];

foreach ($data_to_insert as $field_name => &$field_value) {
    $data_type = $table->getDataTypeForOwnContainer($field_name);
    $field_value = $data_store_formatter->formatByDataType($field_value, $data_type);
}

echo "Data to be inserted: ";
var_dump($data_to_insert);

// Product data is set to false, because data was formatted in middleware
echo "Inserted data: ";
var_dump($table->insert($data_to_insert, product: false));
