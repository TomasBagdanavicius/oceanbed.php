<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Message\Boundary;

$array = [
    'one' => 'Earth',
    'two' => [
        'filepath' => ($_SERVER['DOCUMENT_ROOT'] . '/bin/Text/hello-world.txt'),
    ], [
        'name' => 'three',
        'filepath' => ($_SERVER['DOCUMENT_ROOT'] . '/bin/Text/en-pangram.txt'),
        'filename' => 'pangram.txt',
    ],
];

$boundary = Boundary::fromArray($array);

print $boundary;
