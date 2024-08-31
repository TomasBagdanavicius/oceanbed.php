<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\SearchParams;

$query_component_string = 'fruits=orange&foo=bar';

// Creates new object instance from a string.
$query_component = SearchParams::fromString($query_component_string);

// Adds new pair values.
$query_component->set('fruits', 'apple');
$query_component->set('drinks', 'tea');
$query_component->set('drinks', 'cocoa');
// Can also set as an array.
$query_component->set('fruits', [
    'cherry',
    'grape',
]);

$query_component->sort();

print_r($query_component->toArray());

var_dump($query_component->__toString());
