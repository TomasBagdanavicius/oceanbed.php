<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Message\Boundary;

$array = [
    // Option 1: simple key-value pair.
    'one' => 'Earth',
    // Option 2: name parameter is in the key, whereas value is defined inside a separate array.
    'two' => [
        'contents' => 'Eyes',
    ],
    // Option 3: both params defined separately.
    [
        'name' => 'three',
        'contents' => 'Buttons',
    ],
];

$boundary = Boundary::fromArray($array);

print $boundary;
