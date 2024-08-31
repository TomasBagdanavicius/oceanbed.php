<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Filesystem;
use LWP\Filesystem\FileType\File;
use LWP\Filesystem\Path\PosixPath;
use LWP\Common\Iterators\EmptyStringFilterIterator;
use LWP\Common\Iterators\TrimStringIterator;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Filesystem\Exceptions\FileCreateError;
use LWP\Filesystem\Exceptions\FileDeleteError;
use LWP\Filesystem\Enums\DuplicateHandlingOptionsEnum;
use LWP\Filesystem\FilesystemStats;
use LWP\Filesystem\FileType\Directory;

$filename = realpath(Demo\TEST_PATH . '/bin/filesystem/static/files/multiline.txt');
$file_path = PosixPath::getFilePathInstance($filename);
$file = new File($file_path);

echo "Pathname: ";
var_dump($file->pathname);

echo "Basename: ";
var_dump($file->getBasename());

echo "Filename: ";
var_dump($file->getFilename());

echo "Extension: ";
var_dump($file->getExtension());

echo "Type: ";
var_dump($file->getType());

echo "Count lines: ";
var_dump(count($file));

/* echo "Indexable data: ";
print_r( $file->getIndexableData() );


/* Iterator */

/* foreach( $file as $line_number => $line ) {

    echo $line_number, ' ', $line;
    # Same as
    #echo $file->key(), ' ', $file->current();
}

echo PHP_EOL; */


/* Trim Lines and Filter Out Empty Lines */

/* $trim_string_iterator = new TrimStringIterator($file);
$file_lines = new EmptyStringFilterIterator($trim_string_iterator);

foreach( $file_lines as $line_number => $line ) {

    echo $line_number, ' ', $line, PHP_EOL;
    # Same as
    #echo $file->key(), ' ', $file->current(), PHP_EOL;

endforeach; */


/* Match Condition */

/* $condition_1 = new Condition('basename', 'multiline.txt', ConditionComparisonOperatorsEnum::EQUAL_TO);
echo "Match condition ", $condition_1, ": ";
var_dump($file->matchCondition($condition_1));

$condition_2 = new Condition('basename', 'foobar.txt', ConditionComparisonOperatorsEnum::EQUAL_TO);
echo "Match condition ", $condition_2, ": ";
var_dump($file->matchCondition($condition_2)); */


/* Match Condition Group */

/* $condition_1 = new Condition('basename', 'multiline.txt', ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_group = ConditionGroup::fromCondition($condition_1);
$condition_group->add(new Condition('size', 993));
echo "Match condition group ", $condition_group, ": ";
var_dump($file->matchConditionGroup($condition_group)); */


/* Delete */

/* $filename = realpath(Demo\TEST_PATH . '/bin/filesystem/tmp/to-delete.txt');
$file_path = PosixPath::getFilePathInstance($filename);
$file = new File($file_path);

try {
    $file->delete();
} catch( FileDeleteError $exception ) {
    prl("Error: " . $exception->getMessage());
} finally {
    prl($file->filesystem_stats->getSummaryText());
} */


/* Truncate */

/* $filename = realpath(Demo\TEST_PATH . '/bin/filesystem/tmp/to-truncate.txt');
$file_path = PosixPath::getFilePathInstance($filename);
$file = new File($file_path);

try {
    $file->truncate();
} catch( \Exception $exception ) {
    prl("Error: " . $exception->getMessage());
} finally {
    prl($file->filesystem_stats->getSummaryText());
} */


/* Create */

/* $filename = (Demo\TEST_PATH . '/bin/filesystem/tmp/tmpfile-1.txt');
$file_path = PosixPath::getFilePathInstance($filename);

try {
    $file = File::create($file_path, Filesystem::DEFAULT_BASENAME_DUPLICATE_PATTERN);
    $file->putLine("Hello World!");
} catch( FileCreateError $exception ) {
    prl("Error: " . $exception->getMessage());
} catch( Exception $exception ) {
    prl("Error: " . $exception->getMessage());
} */


/* Duplicate */

/* $filename = (Demo\TEST_PATH . '/bin/filesystem/tmp/duplicate-inside/to-duplicate.txt');
$file_path = PosixPath::getFilePathInstance($filename);

try {
    $file = new File($file_path);
    $new_file = $file->duplicate();
    var_dump($new_file->pathname);
} catch( \Throwable $exception ) {
    prl("Error: " . $exception->getMessage());
} finally {
    prl($file->filesystem_stats->getSummaryText());
} */


/* Copy */

/* $filename = (Demo\TEST_PATH . '/bin/filesystem/tmp/to-copy.txt');
$file_path = PosixPath::getFilePathInstance($filename);

$destination_pathname = (Demo\TEST_PATH . '/bin/filesystem/tmp/to-copy.txt');
$destination_file_path = PosixPath::getFilePathInstance($destination_pathname);

try {
    $file = new File($file_path, new FilesystemStats);
    $new_file = $file->copy($destination_file_path);
    var_dump($new_file->pathname);
} catch( \Throwable $exception ) {
    prl("Error: " . $exception->getMessage());
} finally {
    prl($file->filesystem_stats->getSummaryText());
} */


/* Copy To */

/* $filename = (Demo\TEST_PATH . '/bin/filesystem/tmp/to-copy.txt');
$file_path = PosixPath::getFilePathInstance($filename);

$destination_pathname = (Demo\TEST_PATH . '/bin/filesystem/tmp/copy-destination');
$destination_file_path = PosixPath::getFilePathInstance($destination_pathname);

try {
    $file = new File($file_path);
    $new_file = $file->copyTo(new Directory($destination_file_path));
} catch( \Throwable $exception ) {
    prl("Error: " . $exception->getMessage());
} finally {
    prl($file->filesystem_stats->getSummaryText());
} */


/* Move */

/* $filename = (Demo\TEST_PATH . '/bin/filesystem/tmp/to-move.txt');
$file_path = PosixPath::getFilePathInstance($filename);

$destination_pathname = (Demo\TEST_PATH . '/bin/filesystem/tmp/move-destination/moved.txt');
$destination_file_path = PosixPath::getFilePathInstance($destination_pathname);

try {
    $file = new File($file_path);
    $new_file = $file->move($destination_file_path);
    var_dump($new_file->pathname);
} catch( \Throwable $exception ) {
    prl("Error: " . $exception->getMessage());
} finally {
    prl($file->filesystem_stats->getSummaryText());
} */


/* Move To */

/* $filename = (Demo\TEST_PATH . '/bin/filesystem/tmp/to-move.txt');
$file_path = PosixPath::getFilePathInstance($filename);

$destination_pathname = (Demo\TEST_PATH . '/bin/filesystem/tmp/move-destination');
$destination_file_path = PosixPath::getFilePathInstance($destination_pathname);

$file = new File($file_path);

try {
    $new_file = $file->moveTo(new Directory($destination_file_path));
} catch( \Throwable $exception ) {
    prl("Error: " . $exception->getMessage());
} finally {
    prl($file->filesystem_stats->getSummaryText());
} */


/* Rename */

/* $filename = (Demo\TEST_PATH . '/bin/filesystem/tmp/to-rename.txt');
$file_path = PosixPath::getFilePathInstance($filename);

$file = new File($file_path, new FilesystemStats);

try {
    $new_file = $file->rename('renamed.txt');
} catch( \Throwable $exception ) {
    prl("Error: " . $exception->getMessage());
} finally {
    prl($file->filesystem_stats->getSummaryText());
} */


/* Change Extension */

/* $filename = (Demo\TEST_PATH . '/bin/filesystem/tmp/to-rename.txt');
$file_path = PosixPath::getFilePathInstance($filename);

$file = new File($file_path, new FilesystemStats);

try {
    $new_file = $file->changeExtension('md');
} catch( \Throwable $exception ) {
    prl("Error: " . $exception->getMessage());
} finally {
    prl($file->filesystem_stats->getSummaryText());
} */
