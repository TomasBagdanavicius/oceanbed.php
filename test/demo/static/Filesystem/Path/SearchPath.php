<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Path\SearchPath;
use LWP\Filesystem\Path\Path;

$pathname = 'foo';
$search_path = new SearchPath($pathname, ['/'], '/', SearchPath::ALLOW_EMPTY_SEGMENTS);

var_dump($search_path->getDirname());
var_dump($search_path->getExtension());
print $search_path . PHP_EOL;
