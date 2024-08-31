<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use LWP\Common\Exceptions\ElementNotFoundException;

use function LWP\Common\Array\Arrays\fetchByPath;

$array = [
    'foo' => [
        'bar' => [
            'baz' => "Hello World!",
        ],
        'baz' => 'bar',
    ],
    'bar' => 'baz',
];

try {
    fetchByPath($array, '[foo][baz][bar]');
    $result = false;
} catch (ElementNotFoundException) {
    $result = true;
}

Demo\assert_true(
    $result,
    "Function incorrectly handled unexisting path"
);
