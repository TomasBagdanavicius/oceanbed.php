<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Filesystem\Dataset\FilesystemDirectoryDataset;
use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Path\PosixPath;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Enums\NamedOperatorsEnum;

$pathname = realpath(Demo\TEST_PATH . '/bin/filesystem');
$file_path = PosixPath::getFilePathInstance($pathname);
$directory = new Directory($file_path);
$dataset = new FilesystemDirectoryDataset($directory);

echo "Dataset name: ";
var_dump($dataset->getDatasetName());

echo "Abbreviation: ";
var_dump($dataset->getAbbreviation());

$dataset->setAbbreviation('fdd1');
echo "Set abbreviation: ";
var_dump($dataset->getAbbreviation());

echo "Container list: ";
pr($dataset->getContainerList());

echo "Has own container: ";
var_dump($dataset->hasOwnContainer('pathname'));

echo "All containers exist: ";
var_dump($dataset->containers->containersExist(['pathname', 'basename']));

/* echo "Definition data array: ";
pr($dataset->getDefinitionDataArray()); */

/* echo "Definition collection set: ";
var_dump($dataset->getDefinitionCollectionSet()::class); */

/* echo "Match sensitive containers: ";
var_dump($dataset->containers->getMatchSensitiveContainers()); */

/* echo "Required containers: ";
pr($dataset->getRequiredContainers()); */

/* echo "Next value for unique container: ";
var_dump($dataset->getNextUniqueContainerValue('basename', 'file-1.txt', 255)); */

/* echo "Basic update: ";
var_dump($dataset->updateEntryBasic('pathname', 'to-rename.txt', [
    'extension' => 'md',
])); */

/* echo "Update by: ";
pr($dataset->updateBy('extension', 'txt', [
    'extension' => 'md',
])); */

/* echo "Update integer container value: ";
$dataset->updateIntegerContainerValue('size', 0, 'to-truncate.txt'); */

/* $condition = new Condition('extension', 'txt');
$iterator = $dataset->getContainersByCondition([
    'basename',
    'size',
], $condition);
pr(iterator_to_array($iterator)); */

/* echo "Delete by: ";
$condition = new Condition('extension', 'txt');
pr($dataset->deleteBy($condition)); */

/* echo "Delete entry: ";
var_dump($dataset->deleteEntry('basename', 'file-1.txt')); */


/* Matching Shapes & Matching */

/* $model = clone $dataset->getModel();
$dataset->setupModelPopulateCallbacks($model);
$model->basename = 'file-1.txt'; */

/* $default_unique_case = $dataset->buildDefaultUniqueCase($model, parameterize: false, rcte_id: null);
echo "Shape for matching: ";
var_dump($default_unique_case->__toString()); */

/* echo "Standard unique case: ";
$standard_unique_case = $dataset->buildStandardUniqueCase($model);
var_dump($standard_unique_case->__toString()); */


/* Contains Container Value(s) */

#var_dump($dataset->containsContainerValue('basename', 'abc.txt'));
#var_dump($dataset->containsContainerValue('basename', 'abcd.txt'));

/* var_dump($dataset->containsContainerValues('basename', [
    'abc.txt',
    'abcd.txt',
])); */
/* $condition_group = ConditionGroup::fromCondition(new Condition('size', 5));
var_dump($dataset->containsContainerValues('basename', [
    'abc.txt',
    'abcde.txt',
], $condition_group)); */
