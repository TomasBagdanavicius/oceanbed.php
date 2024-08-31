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
$collection->set('three', 'baz');
$collection->set('two', 'bar', pos: 1);
$collection->add(10);

Demo\assert_true(
    $collection->toArray() === [
        'one' => 'foo',
        'two' => 'bar',
        'three' => 'baz',
        10,
    ],
    "Incorrect payload"
);
