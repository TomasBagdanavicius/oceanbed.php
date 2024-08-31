<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\FileCollection;
use LWP\Filesystem\FileType\File;
use LWP\Filesystem\Path\PosixPath;

$filename = ($_SERVER['DOCUMENT_ROOT'] . '/bin/Text/paragraphs.txt');
$file_path = PosixPath::getFilePathInstance($filename);
$file = new File($file_path);

$file_collection = new FileCollection();
$file_collection->add($file);

var_dump(count($file_collection));
