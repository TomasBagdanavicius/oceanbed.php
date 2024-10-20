<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
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

$flattened_array = flattenWithKeyPath($array);

Demo\assert_true(
    $flattened_array === [
        'part1' => "Introduction",
        'part2[0]' => "The Beginning",
        'part2[1]' => "The Story",
        'part2[2][what]' => "What happened?",
        'part2[2][when]' => "When it happened?",
        'part2[2][why]' => "Why it happened?",
        'part3' => "The End",
    ],
    "Flattened array does not match the expected format"
);
