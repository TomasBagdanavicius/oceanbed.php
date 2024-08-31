<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Array\IndexableArrayCollection;
use LWP\Common\Conditions\Condition;

$collection = new IndexableArrayCollection();

$collection->add([
    'id' => 1,
    'name' => 'John',
    'age' => 35,
    'occupation' => 'Teacher',
    'height' => 1.92,
]);

$collection->add([
    'id' => 3,
    'name' => 'Jane',
    'age' => 52,
    'occupation' => 'Lawyer',
    'height' => 1.71,
]);

$collection->add([
    'id' => 4,
    'name' => 'Dave',
    'age' => 41,
    'occupation' => 'Teacher',
    'height' => 1.79,
]);

$collection->add([
    'id' => 10,
    'name' => 'John',
    'age' => 31,
    'occupation' => 'Architect',
    'height' => 1.88,
]);

$condition = new Condition('name', "John");
$filtered_collection = $collection->matchCondition($condition);

Demo\assert_true(
    $filtered_collection->toArray() === [
        0 => [
            'id' => 1,
            'name' => 'John',
            'age' => 35,
            'occupation' => 'Teacher',
            'height' => 1.92,
        ],
        3 => [
            'id' => 10,
            'name' => 'John',
            'age' => 31,
            'occupation' => 'Architect',
            'height' => 1.88,
        ],
    ],
    "This is my error message"
);
