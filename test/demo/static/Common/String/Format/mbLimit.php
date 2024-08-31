<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\String\Format;

var_dump(mb_strlen('The quick brown fox '));

print(Format::mbLimit('The quick brown fox jumps over the lazy dog', 20) . PHP_EOL);
print(Format::mbLimit('The quick brown fox  jumps over the lazy dog', 20) . PHP_EOL);
print(Format::mbLimit('The quick brown fox jumps over the lazy dog', 19) . PHP_EOL);
print(Format::mbLimit('The quick brown fox jumps over the lazy dog', 21) . PHP_EOL);
print(Format::mbLimit('The quick brown fox jumps over the lazy dog', 21, excess: 3) . PHP_EOL);
print(Format::mbLimit('The quick brown fox jumps over the lazy dog', 41) . PHP_EOL);
print(Format::mbLimit('The quick brown fox jumps over the lazy dog', 43) . PHP_EOL);

print(Format::mbLimit('Šis ąžuolas tikrai gražus ir aukštas', 5) . PHP_EOL);
print(Format::mbLimit('Šis ąžuolas tikrai gražus ir aukštas', 20) . PHP_EOL);
