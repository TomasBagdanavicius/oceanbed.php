<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Array\ArrayCollection;

$collection = new ArrayCollection();
$collection->set('one', 'foo');
$collection->set('two', 'bar');
$collection->set('three', 'baz');

$filtered_collection = $collection->filterByValues(['foo', 'baz']);

Demo\assert_true(
    $filtered_collection->toArray() === [
        'one' => 'foo',
        'three' => 'baz',
    ],
    "Incorrect payload"
);
