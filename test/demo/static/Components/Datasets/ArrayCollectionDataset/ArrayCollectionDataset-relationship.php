<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(__DIR__ . '/shared/data-2.php');

use LWP\Components\Datasets\ArrayCollectionDataset\ArrayCollectionDatabase;
use LWP\Components\Datasets\ArrayCollectionDataset\ArrayCollectionDataset;
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Datasets\Relationships\Relationship;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Common\Criteria;
use LWP\Common\String\Clause\SortByComponent;

$column_data_array = $data;

$definition_data_array_2 = [
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
    'short_name' => [
        'type' => 'string',
        'description' => "Short name",
    ],
];

$column_data_array_2 = [
    [
        'id' => 1,
        'name' => 'United States',
        'short_name' => "US",
    ], [
        'id' => 2,
        'name' => 'United Kingdom',
        'short_name' => "UK",
    ],
];

$database = new ArrayCollectionDatabase();

$array_collection_dataset_1 = $database->initDataset('dataset_1', [
    $column_data_array,
    $definition_data_array,
    'primary_container_name' => 'id',
]);

$array_collection_dataset_2 = $database->initDataset('dataset_2', [
    $column_data_array_2,
    $definition_data_array_2,
    'primary_container_name' => 'id',
]);

$lazy_datasets = [
    [
        'dataset_1',
        (static function () use ($array_collection_dataset_1): DatasetInterface {
            return $array_collection_dataset_1;
        }),
    ], [
        'dataset_2',
        (static function () use ($array_collection_dataset_2): DatasetInterface {
            return $array_collection_dataset_2;
        }),
    ]
];

$relationship = new Relationship(
    name: 'relationship-1',
    id: 1,
    dataset_array: $lazy_datasets,
    type_code: 1010000000,
    column_list: [
        'country',
        'id',
    ]
);

$database->setRelationship($relationship);

$select_handle = $array_collection_dataset_1->getSelectHandle([
    /* Intrinsic */
    'id',
    'name',
    'age',
    #'occupation',
    #'height',
    'country',
    /* Extrinsic */
    'relationship-1_name',
    'country_short_name' => [
        'relationship' => 'relationship-1',
        'property_name' => 'short_name',
    ],
    /* 'country_name' => [
        'relationship' => 'relationship-1',
        'property_name' => 'name',
    ], */
]);

#pr($select_handle->getDefinitionDataArray());
#pre($select_handle::parseRelationshipIdentifier('relationship-1_1_1_name'));
#var_dump(array_keys($select_handle->getExtrinsicContainerList()));
#var_dump($select_handle->hasExtrinsicContainer('country_short_name'));
#var_dump($select_handle->getRelationshipForExtrinsicContainer('country_short_name')?->name);
#var_dump($select_handle->getTheOtherDatasetForExtrinsicContainer('country_short_name')?->name);

/* $select_handle->getModel();
$select_handle->getExtrinsicContainerList(); */

$fetch_manager = $array_collection_dataset_1->getFetchManager();

/* $action_params = $fetch_manager::getModelForActionType('read');
$data_server_context = $fetch_manager->list($action_params); */

$data_server_context = $fetch_manager->getSingleByPrimaryContainer($select_handle, 3);

foreach ($data_server_context as $model) {
    var_dump(
        $model->id,
        $model->name,
        $model->age,
        $model->country,
        $model->relationship1_name,
        $model->country_short_name,
    );
}
