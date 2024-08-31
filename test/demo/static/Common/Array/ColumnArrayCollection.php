<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Array\ColumnArrayCollection;

// Provide list explicitly.
$element_list = [
    'id',
    'name',
    'age',
    'occupation',
    'height',
    // Tests alien element names
    #'sex',
];
// Make it auto-detect element list.
#$element_list = null;

$primary_set = [
    [
        'id' => 1,
        'name' => 'John',
        'age' => 35,
        'occupation' => 'Teacher',
        'height' => 1.92,
    ],
];

$column_array_collection = new ColumnArrayCollection($primary_set, $element_list);

$column_array_collection->add([
    'id' => 3,
    'name' => 'Jane',
    'age' => 52,
    'occupation' => 'Lawyer',
    'height' => 1.71,
    #'sex' => 'female',
]);

$column_array_collection->add([
    'id' => 10,
    'name' => 'John',
    'age' => 31,
    'occupation' => 'Architect',
    'height' => '1.88',
    #'sex' => 'male',
]);

pr($column_array_collection->toArray());


/* Select Elements */

$filtered_array_collection = $column_array_collection->selectElements([
    'id',
    'name',
]);

pre($filtered_array_collection->toArray());
