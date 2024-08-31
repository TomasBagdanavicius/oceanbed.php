<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

$table = $database->getTable('data_types');

$data_to_insert = [
    'title' => 'Table Insert Product',
    'my_datetime' => '31st December 2021 4.30 p.m.', // Requires adjustment.
    #'my_datetime' => '2022-01-01 08:00:00',
    'my_decimal' => '1 234 567,89', // Requires adjustment.
    #'my_decimal' => '12345.67',
    'my_nullable' => null,
    'my_int' => false, // Requires adjustment. Expecting zero (0) product.
    #'my_int' => 0,
    'my_unique_varchar' => 'A', // Requires product.
    #'my_unique_int' => 1, // Will not adjust a unique int, because that is highly debated.
    #'unexisting_field' => 'Hello World!', // Unexisting field (error handling works when product is set to true).
];

print "Insert ID: ";
var_dump($table->insert($data_to_insert));
