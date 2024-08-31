<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');
require_once(PROJECTS_PATH . '/data-project/src/private/Autoload.php');

use LWP\Components\Datasets\Relationships\Relationship;
use LWP\Components\Datasets\ConsistentDatasetCollection;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Components\Datasets\Relationships\RelationshipNodeStorageInterface;
use LWP\Components\Datasets\Attributes\AnyDatasetAttribute;
use Data\App;

$app = new App();
$relationship_nodes = $app->module_factory->getModuleByName('relationship-nodes');
$relationship_nodes_dataset = $relationship_nodes->dataset;

$lazy_datasets = [
    [
        'temp',
        function () use ($database): DatasetInterface {
            return $database->getTable('temp');
        },
    ], [
        'test',
        function () use ($database): DatasetInterface {
            return $database->getTable('test');
        },
    ], [
        'empty',
        function () use ($database): DatasetInterface {
            return $database->getTable('empty');
        },
    ],
    #new AnyDatasetAttribute,
];

/*
$dataset_collection = new ConsistentDatasetCollection;
$dataset_collection->add($lazy_datasets[0][1]());
$dataset_collection->add($lazy_datasets[1][1]());
$dataset_collection->add($lazy_datasets[2][1]());
*/

$relationship = new Relationship(
    name: 'temp-to-test-to-empty',
    id: 1,
    dataset_array: $lazy_datasets,
    type_code: 1010100000,
    column_list: [
        'id',
        'id',
        'id',
    ],
    node_dataset: $relationship_nodes_dataset,
);

pr($relationship->getIndexableData());
print "Is ambiguous: ";
var_dump($relationship->isAmbiguous());
print "Is ambiguous for: ";
var_dump($relationship->isAmbiguousFor($lazy_datasets[0][1]()));
print "Contains any: ";
var_dump($relationship->containsAny());

/* Perspective */

#$perspective = $relationship->getPerspectiveByContainerNumber(3);
#$perspective = $relationship->getPerspectiveByContainerLetter('c');
$perspective = $relationship->getPerspectiveByDataset($lazy_datasets[1][1]());
print "Type code: ";
var_dump($perspective->type_code);
print "Dataset class: ";
var_dump($perspective->dataset::class);
print "Is any: ";
var_dump($perspective->is_any);
print "Type name: ";
var_dump($perspective->getTypeName());
print "Column name: ";
var_dump($perspective->container_name);
print "Is column primary: ";
var_dump($perspective->isContainerPrimary());
