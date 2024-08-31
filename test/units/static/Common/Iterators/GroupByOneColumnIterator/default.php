<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Iterators\GroupByOneColumnIterator;

$array = [
    [
        'id' => 10,
        'name' => 'John',
        'city' => 'London',
    ],[
        'id' => 2,
        'name' => 'Jane',
        'city' => 'London',
    ],[
        'id' => 1,
        'name' => 'John',
        'city' => 'London',
    ],[
        'id' => 7,
        'name' => 'Camille',
        'city' => 'Paris',
    ],[
        'id' => 11,
        'name' => 'John',
        'city' => 'Boston',
    ],[
        'id' => 12,
        'name' => 'John',
        'city' => 'New York',
    ],
];

$iterator = new \ArrayIterator($array);
// Group by name container.
$iterator = new GroupByOneColumnIterator($iterator, 'name', "Name");
// Loop through all elements and collect storage
$unique_names = iterator_to_array($iterator);
$storage = $iterator->getStorage();

Demo\assert_true($storage === [
    [
        'name' => "John",
        'id' => 10,
        'city' => [
            "London",
            "Boston",
            "New York",
        ]
    ], [
        'name' => "Jane",
        'id' => 2,
        'city' => "London",
    ], [
        'name' => "Camille",
        'id' => 7,
        'city' => "Paris",
    ]
], "Unexpected result");
