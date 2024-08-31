<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\String\Str;

// In contrast to "str_replace" or "preg_replace" this function tries not to replace a previously inserted value by collecting an array of reserved/locked intervals where further insertions cannot take place.

$subject = "The quick, brown fox jumps over a lazy dog.";

$search = [
    'quick',
    'lazy',
    'dog',
    'fox',
];

$replace = [
    'lazy',
    'quick',
    'fox',
    'dog',
];

prl("Standard string replace: " . str_replace($search, $replace, $subject));
prl("String replace once: " . Str::replaceOnce($search, $replace, $subject));

/* Case 2 */

$subject = 'YYm Y L mm mY';

$search = [
    'm',
    'Y',
];

$replace = [
    'm' => '%m',
    'Y' => '%Y',
];

prl(Str::replaceOnce($search, $replace, $subject));
