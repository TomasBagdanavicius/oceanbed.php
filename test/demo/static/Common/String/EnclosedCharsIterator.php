<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\String\EnclosedCharsIterator;

$chars = [
    // Format:
    // opening char => [closing char, escaped]
    '"' => ['"', true],
    '\'' => ['\'', true],
    '{' => ['}', false],
];

$enclosed_chars_iterator = new EnclosedCharsIterator('Hello "New" World!', $chars);
#$enclosed_chars_iterator = new EnclosedCharsIterator('Hello "New" World!', $chars, EnclosedCharsIterator::CURRENT_STRIPPED_OFF);
#$enclosed_chars_iterator = new EnclosedCharsIterator('This {meal} is \{very\} delicious!', $chars);

foreach ($enclosed_chars_iterator as $key => $val) {

    var_dump($val);
    var_dump($enclosed_chars_iterator->hasEnclosingChars());
}
