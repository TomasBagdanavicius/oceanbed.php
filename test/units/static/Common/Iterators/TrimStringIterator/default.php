<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Iterators\TrimStringIterator;

$array = [
    'one',
    ' two',
    'three  ',
    '  four ',
    '   five  ',
    123,
];
$iterator = new \ArrayIterator($array);
$iterator = new TrimStringIterator($iterator);

Demo\assert_true(iterator_to_array($iterator) === [
    'one',
    'two',
    'three',
    'four',
    'five',
    123
], "This is my error message");
