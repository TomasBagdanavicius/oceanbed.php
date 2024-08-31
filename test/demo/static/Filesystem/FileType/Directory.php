<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Path\PosixPath;
use LWP\Filesystem\Exceptions\FileTruncateError;
use LWP\Filesystem\Exceptions\DirectoryCreateError;
use LWP\Filesystem\FilesystemStats;

$filename = realpath(Demo\TEST_PATH . '/bin/filesystem/read');
$file_path = PosixPath::getFilePathInstance($filename);
$directory = new Directory($file_path);

echo "Path name: ";
prl($directory->pathname);

echo "Real size: ";
var_dump($directory->getRealSize());

echo "Count: ";
var_dump(count($directory));

echo "Extension: ";
var_dump($directory->getExtension());

/* echo "Indexable data: ";
print_r($directory->getIndexableData()); */


/* Create */

/* // Relative (to directory of this file)
$pathname = 'foo/bar';
// Absolute
$pathname = (Demo\TEST_PATH . '/bin/filesystem/tmp/create-inside/foo/bar');

$file_path = PosixPath::getFilePathInstance($pathname);
$filesystem_stats = new FilesystemStats();

try {
    $new_directory = Directory::create($file_path, filesystem_stats: $filesystem_stats);
} catch( DirectoryCreateError $exception ) {
    prl("Error: " . $exception->getMessage());
} finally {
    prl($filesystem_stats->getSummaryText());
} */


/* Delete */

/* $filename = realpath(Demo\TEST_PATH . '/bin/filesystem/tmp/to-delete');
$file_path = PosixPath::getFilePathInstance($filename);
$directory = new Directory($file_path);

try {
    $directory->delete();
} catch( FileDeleteError $exception ) {
    prl("Error: " . $exception->getMessage());
} */


/* Truncate */

/* $filename = realpath(Demo\TEST_PATH . '/bin/filesystem/tmp/to-truncate');
$file_path = PosixPath::getFilePathInstance($filename);
$directory = new Directory($file_path);

try {
    $directory->truncate();
} catch( FileTruncateError $exception ) {
    prl("Error: " . $exception->getMessage());
} */


/* Is empty */

/* $filename = realpath(Demo\TEST_PATH . '/bin/filesystem/empty');
$file_path = PosixPath::getFilePathInstance($filename);
$directory = new Directory($file_path);
echo "Is empty: ";
var_dump($directory->isEmpty()); */
