<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\String\Clause\SortByComponent;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\multisortPrepare;
use function LWP\Common\Array\Arrays\sortByColumns;

/* Known issue: when volume consists of integers and sort order is ASC, matching
integers are not sorted by the order they are naturally in the array. */

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

$sort_by_str = 'name ASC, city DESC, id DESC';
$sort_by_component = SortByComponent::fromString($sort_by_str);

// Build the columns by "sort by" fields.
$array_columns = multisortPrepare($array, $sort_by_component);

// Sort the array.
sortByColumns($array, $array_columns, $sort_by_component);

print_r($array);
