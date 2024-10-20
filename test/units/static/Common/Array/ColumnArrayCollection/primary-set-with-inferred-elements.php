<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Array\ColumnArrayCollection;
use LWP\Common\Array\Exceptions\ColumnKeysMismatchException;

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

Demo\assert_true($column_array_collection->toArray() === [[
    'id' => 1,
    'name' => 'John',
    'age' => 35,
    'occupation' => 'Teacher',
    'height' => 1.92,
]], "Array output did not match the expected content");
