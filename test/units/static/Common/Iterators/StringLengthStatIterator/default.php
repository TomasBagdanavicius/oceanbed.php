<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Iterators\StringLengthStatIterator;
use LWP\Filesystem\FileType\File;
use LWP\Filesystem\Path\PathEnvironmentRouter;

$pathname = ($_SERVER['DOCUMENT_ROOT'] . '/bin/Text/paragraphs.txt');
$file_path = PathEnvironmentRouter::getStaticInstance()::getFilePathInstance($pathname);
$file = new File($file_path);
$iterator = new StringLengthStatIterator($file);

// Run through and store data
iterator_to_array($iterator);

[$longest_str_len, $shortest_str_len] = $iterator->getStorage();

Demo\assert_true(
    $longest_str_len === 474 && $shortest_str_len === 2,
    "Got unexpected results"
);
