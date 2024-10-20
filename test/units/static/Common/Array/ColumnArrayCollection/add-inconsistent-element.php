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

try {
    $column_array_collection->add([
        'id' => 1,
        'name' => 'John',
        'age' => 35,
        'occupation' => 'Teacher',
        'height' => 1.92,
        // This element is not available in the first group
        'sex' => 'male',
    ], );
    $result = false;
} catch (ColumnKeysMismatchException) {
    $result = true;
}

Demo\assert_true($result, "Collection incorrectly allowed to add an inconsistent element");
