<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Iterators\ArrayColumnSelectIterator;
use LWP\Common\Array\Exceptions\ColumnKeysMismatchException;

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
        # age missing here
        'occupation' => 'Architect',
        'height' => '1.88',
        'country' => 1,
    ],
];

$iterator = new \ArrayIterator($array);
$iterator = new ArrayColumnSelectIterator($iterator, [
    'name',
    'age',
], options: ArrayColumnSelectIterator::THROW_WHEN_ELEMENT_NOT_FOUND);

try {
    $array = iterator_to_array($iterator);
    $result = false;
} catch (ColumnKeysMismatchException) {
    $result = true;
}

Demo\assert_true($result, "Expected exception was not caught");
