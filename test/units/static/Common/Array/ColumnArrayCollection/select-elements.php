<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Array\ColumnArrayCollection;

$primary_set = [
    [
        'id' => 1,
        'name' => 'John',
        'age' => 35,
        'occupation' => 'Teacher',
        'height' => 1.92,
    ],
];

$column_array_collection = new ColumnArrayCollection($primary_set);

$column_array_collection->add([
    'id' => 3,
    'name' => 'Jane',
    'age' => 52,
    'occupation' => 'Lawyer',
    'height' => 1.71,
]);

$column_array_collection->add([
    'id' => 10,
    'name' => 'John',
    'age' => 31,
    'occupation' => 'Architect',
    'height' => '1.88',
]);

$filtered_collection = $column_array_collection->selectElements([
    'name',
    'age',
    'occupation'
]);

Demo\assert_true($filtered_collection->toArray() === [
    0 => [
        'name' => 'John',
        'age' => 35,
        'occupation' => "Teacher",
    ], 1 => [
        'name' => 'Jane',
        'age' => 52,
        'occupation' => "Lawyer",
    ], 2 => [
        'name' => 'John',
        'age' => 31,
        'occupation' => "Architect",
    ]
], "Filtered collection does not meet the expected output");
