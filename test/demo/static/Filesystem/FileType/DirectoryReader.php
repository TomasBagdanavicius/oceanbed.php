<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\FileType\DirectoryReader;
use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Path\PosixPath;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;

$file_path = PosixPath::getFilePathInstance(realpath(Demo\TEST_PATH . '/bin/filesystem/read'));
$directory = new Directory($file_path);

$directory_reader = new DirectoryReader($directory, DirectoryReader::RECURSE | DirectoryReader::CHILD_FIRST);
#$directory_reader->conditions(new Condition('basename', 'abc', ConditionComparisonOperatorsEnum::CONTAINS));
#$directory_reader->sort('type DESC, filename ASC, extension ASC');
#$directory_reader->limitSize(6161);
#$directory_reader->limit(5);
#$directory_reader->offset(2);

foreach ($directory_reader as $key => $file) {

    #var_dump($key);
    echo $file->pathname, ' ', $file::class, PHP_EOL;
}
