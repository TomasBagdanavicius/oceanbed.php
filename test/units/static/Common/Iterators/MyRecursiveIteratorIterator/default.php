<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Iterators\MyRecursiveIteratorIterator;

$array_tree = [
    'one' => [
        'one.1',
        'one.2',
        'one.3' => [
            'one.3.1',
        ],
    ],
    'two' => [
        'two.1' => [
            'two.1.1',
            'two.1.2',
        ],
        'two.2',
    ],
    'three',
];
$iterator = new MyRecursiveIteratorIterator(
    new RecursiveArrayIterator($array_tree),
    MyRecursiveIteratorIterator::SELF_FIRST
);
$paths = [];

foreach ($iterator as $key => $val) {
    $paths[] = $iterator->getPath();
}

Demo\assert_true($paths === [
    'one',
    'one/one.1',
    'one/one.2',
    'one/one.3',
    'one/one.3/one.3.1',
    'two',
    'two/two.1',
    'two/two.1/two.1.1',
    'two/two.1/two.1.2',
    'two/two.2',
    'three',
], "Output did not match the expected result");
