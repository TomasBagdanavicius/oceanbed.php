<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(__DIR__ . '/shared/custom-descriptor.php');

use LWP\Components\Datasets\ArrayCollectionDataset\ArrayCollectionDatabase;
use LWP\Components\Datasets\ArrayCollectionDataset\ArrayCollectionDataset;
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Datasets\Attributes\SelectAllAttribute;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;

$definition_data_array = [
    'id' => [
        'type' => 'integer',
        'min' => 1,
        'unique' => true,
        'description' => "Primary",
    ],
    'name' => [
        'type' => 'string',
        'description' => "Name",
    ],
    'age' => [
        'type' => 'integer',
        'description' => "Age",
    ],
    'occupation' => [
        'type' => 'string',
        'description' => "Occupation",
    ],
    'height' => [
        'type' => 'number',
        'description' => "Height",
    ],
];

$column_data_array = [
    [
        'id' => 1,
        'name' => 'John',
        'age' => 35,
        'occupation' => 'Teacher',
        'height' => '1.92',
    ], [
        'id' => 3,
        'name' => 'Jane',
        'age' => 52,
        'occupation' => 'Lawyer',
        'height' => '1.71',
    ], [
        'id' => 10,
        'name' => 'John',
        'age' => 31,
        'occupation' => 'Architect',
        'height' => '1.88',
        #'sex' => 'male',
    ],
];

$database = new ArrayCollectionDatabase(CustomArrayCollectionDatabaseDescriptor::class);

$dataset = $database->initDataset('my_dataset', [
    $column_data_array,
    $definition_data_array,
]);

echo "Name: ";
var_dump($dataset->getDatasetName());

echo "Abbreviation: ";
var_dump($dataset->getAbbreviation());

$dataset->setAbbreviation('md');
echo "Set abbreviation: ";
var_dump($dataset->getAbbreviation());

echo "Container list: ";
pr($dataset->getContainerList());

$database = $dataset->getDatabase();

echo "Database class: ";
var_dump($database::class);

echo "Datasets in database count: ";
var_dump(count($database->collection));

/* echo "Definition data array: ";
pr($dataset->getDefinitionDataArray()); */

/* echo "Definition collection set: ";
var_dump($dataset->getDefinitionCollectionSet()::class); */

/* echo "Match sensitive containers: ";
var_dump($dataset->getMatchSensitiveContainers()); */

/* echo "By condition object: ";
$condition = new Condition('name', 'John');
$condition_group = ConditionGroup::fromCondition($condition);
var_dump($dataset->byConditionObject($condition_group)); */

/* echo "Required containers: ";
var_dump($dataset->getRequiredContainers()); */

/* echo "Custom descriptor: ";
var_dump($dataset->custom_descriptor); */

/* echo "Count by condition with primary excluded: ";
$condition = new Condition('name', 'John');
var_dump($dataset->countByConditionWithPrimaryExcluded($condition, [10])); */

/* echo "Update basic: ";
var_dump($dataset->updateEntryBasic('id', 10, [
    'occupation' => 'Pharmacist',
]));
$condition = new Condition('occupation', 'Pharmacist');
pr($dataset->byConditionObject($condition)->toArray()); */

/* echo "Update by: ";
var_dump($dataset->updateBy('name', "John", [
    'age' => 40,
])); */

/* echo "Update integer container value: ";
vare($dataset->updateIntegerContainerValue('age', 33, 1)); */

/* $condition = new Condition('name', 'John');
$iterator = $dataset->getContainersByCondition([
    'name',
    'occupation',
], $condition);
pr(iterator_to_array($iterator)); */

/* echo "Delete by: ";
$condition = new Condition('name', 'John');
pr($dataset->deleteBy($condition));
// See if it was deleted
pr($dataset->byConditionObject($condition)->toArray()); */

/* echo "Delete entry: ";
var_dump($dataset->deleteEntry('id', 10)); */


/* Contains Container Value(s) */

#var_dump($dataset->containsContainerValue('name', 'Jane'));
/* $condition_group = ConditionGroup::fromArray([
    'age' => 52,
    'occupation' => 'Lawyer',
]);
var_dump($dataset->containsContainerValue('name', 'Jane', $condition_group)); */
/* $condition_group = ConditionGroup::fromArray([
    'age' => 52,
    'occupation' => 'Pharmacist',
]);
var_dump($dataset->containsContainerValue('name', 'Jane', $condition_group)); */
#var_dump($dataset->containsContainerValue('name', 'Bernard'));

/* var_dump($dataset->containsContainerValues('name', [
    'Jane',
    'John',
])); */
/* $condition_group = ConditionGroup::fromCondition(new Condition('occupation', 'Teacher'));
var_dump($dataset->containsContainerValues('name', [
    'Jane',
    'John',
], $condition_group)); */
