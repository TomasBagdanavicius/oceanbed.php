<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
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
    'as' => 'inside',
    'two' => [
        'two.1',
    ],
];

$iterator = new MyRecursiveIteratorIterator(
    new RecursiveArrayIterator($array_tree),
    MyRecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $key => $val) {

    if ($iterator->canChildren()) {
        echo $iterator->getPath(), PHP_EOL;
    }

    echo '- ', $key, PHP_EOL;
    #print_r( $val );
    #if( !is_array($val) ) echo PHP_EOL;

    if ($key == 'one.3') {
        $iterator->lockChildren();
    }
}
