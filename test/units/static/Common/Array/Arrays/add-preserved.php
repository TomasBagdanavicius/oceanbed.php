<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\addPreserved;

$array = [
    'one' => "Vienas",
    'two' => "Du",
    'three' => "Trys",
];

addPreserved($array, 'two', 'Zwei');

Demo\assert_true(
    $array === [
        'one' => "Vienas",
        'two' => [
            "Du",
            "Zwei",
        ],
        'three' => "Trys",
    ],
    "Array does not match the expected output"
);
