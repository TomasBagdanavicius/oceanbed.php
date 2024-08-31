<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Array\ArrayCollection;

$data = [
    2 => 'foo',
    5 => 'bar',
];

$collection = new ArrayCollection($data);

Demo\assert_true(
    $collection->getNextIndexId() === 6,
    "Incorrect next index number"
);
