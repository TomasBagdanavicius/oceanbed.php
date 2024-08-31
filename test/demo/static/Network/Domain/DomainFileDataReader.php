<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Path\PosixPath;
use LWP\Network\Domain\DomainFileDataReader;

$filename = ($_SERVER['DOCUMENT_ROOT'] . '/storage/downloads/Data/public-suffix-list/files/public_suffix_list.dat');
$file_path = PosixPath::getFilePathInstance($filename);
$data_reader = new DomainFileDataReader($file_path);

echo "Find \"com\": ";
var_dump($data_reader->containsEntry('com'));
echo "Extract from \"www.domain.co.uk\": ";
var_dump($data_reader->getPublicSuffix('www.domain.co.uk'));
