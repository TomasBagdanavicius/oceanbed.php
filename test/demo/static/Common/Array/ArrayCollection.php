<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Array\ArrayCollection;

$data = [
    'Zero',
];

// Adds the initial array.
$collection = new ArrayCollection($data);

/* Manipulates the array collection */

// Adds a new value and returns the key for the new value.
var_dump($collection->add('Two'));
// Adds a new entry with a chosen key, value, and position.
var_dump($collection->set('one', 'One', pos: 1));
var_dump($collection->add('Three'));
var_dump($collection->set('10', 'Ten'));
var_dump($collection->set('next', 'Eleven'));
var_dump($collection->add('Five'));
// Updates value by key.
var_dump($collection->update('next', 'Four'));
// Removes value by key.
var_dump($collection->remove('10'));
// Checks if value "One" is available.
var_dump($collection->contains('One'));

print_r($collection->toArray());

/* Creates a filtered collection by value */

$filtered_collection = $collection->filterByValues([
    'One',
    'Two',
    'Three',
]);

print_r($filtered_collection->toArray());

/* Creates filtered collection by keys */

$filtered_collection = $collection->filterByKeys([
    '0',
    'one',
    'next',
]);

print_r($filtered_collection->toArray());
