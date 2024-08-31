<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Path\PosixPath;
use LWP\Filesystem\Files\PublicSuffixListFile;

$filename = ($_SERVER['DOCUMENT_ROOT'] . '/storage/downloads/Data/public-suffix-list/files/public_suffix_list.dat');
$file_path = PosixPath::getFilePathInstance($filename);
$file = new PublicSuffixListFile($file_path);
$c = 1;

foreach ($file as $processed_line_number => $data) {

    print_r($data);

    $c++;

    // Restrict due to optimisation considerations.
    if ($c > 10) {
        break;
    }
}
