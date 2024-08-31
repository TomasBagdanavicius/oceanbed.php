<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\String\EnclosedCharsIterator;

$input = [
    'Lorem \"ipsum\" dolor sit \'amet\', consectetur adipisicing "elit"',
    [
        '"' => ['"', true],
        '\'' => ['\'', true],
    ],
];

$expected_output = [
    'Lorem \"ipsum\" dolor sit ',
    '\'amet\'',
    ', consectetur adipisicing ',
    '"elit"',
];

$enclosed_chars_iterator = new EnclosedCharsIterator(...$input);

$result = [];

foreach ($enclosed_chars_iterator as $segment) {
    $result[] = $segment;
}

Demo\assert_true(
    $result === $expected_output,
    "Result does not match expected output"
);
