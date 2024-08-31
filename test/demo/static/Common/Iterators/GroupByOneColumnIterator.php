<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
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

foreach ($iterator as $key => $value) {

    var_dump($value);
}

pr($iterator->getStorage());
