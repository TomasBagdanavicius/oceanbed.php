<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\FileType\FileFormats\PhpTokenIterator;

$pathname = (Demo\TEST_PATH . '/bin/php-file.php');
$php_code = file_get_contents($pathname);
$token_iterator = new PhpTokenIterator($php_code);

foreach ($token_iterator as $token) {

    pr($token);
}
