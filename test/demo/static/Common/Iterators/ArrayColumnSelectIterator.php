<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Iterators\ArrayColumnSelectIterator;

$array = [
    [
        'id' => 1,
        'name' => 'John',
        'age' => 35,
        'occupation' => 'Teacher',
        'height' => '1.92',
        'country' => 1,
    ], [
        'id' => 3,
        'name' => 'Jane',
        'age' => 52,
        'occupation' => 'Lawyer',
        'height' => '1.71',
        'country' => 2,
    ], [
        'id' => 10,
        'name' => 'John',
        'age' => 31,
        'occupation' => 'Architect',
        'height' => '1.88',
        'country' => 1,
    ],
];

$iterator = new \ArrayIterator($array);
$iterator = new ArrayColumnSelectIterator($iterator, [
    'name',
    'age',
]);

foreach ($iterator as $key => $value) {
    print_r($value);
}
