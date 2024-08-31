<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Array\ColumnArrayCollection;
use LWP\Common\Array\Exceptions\ColumnKeysMismatchException;

// Provide list explicitly.
$element_list = [
    'id',
    'name',
    'age',
    'occupation',
    'height',
    // Tests alien element names
    'sex',
];

$primary_set = [
    [
        'id' => 1,
        'name' => 'John',
        'age' => 35,
        'occupation' => 'Teacher',
        'height' => 1.92,
    ],
];

try {
    $column_array_collection = new ColumnArrayCollection($primary_set, $element_list);
    $result = false;
} catch (ColumnKeysMismatchException) {
    $result = true;
}

Demo\assert_true($result, "Constructor did not throw a mismatch exception");
