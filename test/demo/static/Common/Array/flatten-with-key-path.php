<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays');
use function LWP\Common\Array\Arrays\flattenWithKeyPath;

$array = [
    'part1' => 'Introduction',
    'part2' => [
        'The Beginning',
        'The Story',
        [
            'what' => 'What happened?',
            'when' => 'When it happened?',
            'why' => 'Why it happened?',
        ],
    ],
    'part3' => 'The End',
];

/* Case 1: regular */

$flattened_array = flattenWithKeyPath($array);

print_r($flattened_array);

/* Case 2: custom key */

$flattened_array = flattenWithKeyPath($array, function (?string $prefix, int|string $key): string {

    return (!$prefix)
        ? $key
        : ($prefix . '.' . $key);

});

print_r($flattened_array);

/* Case 3: custom key */

$flattened_array = flattenWithKeyPath($array, function (?string $prefix, int|string $key): string {

    return ((string)$prefix . '{' . $key . '}');

});

print_r($flattened_array);
