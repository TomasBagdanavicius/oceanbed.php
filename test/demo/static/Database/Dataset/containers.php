<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/demo/shared/test-relationship.php');

$dataset1->setRelatedReadContainer('readparam', [
    'relationship' => 'relationship-1',
    'property_name' => 'title',
]);

$dataset1->setRelatedStoreContainer('storeparam', [
    'relationship' => 'relationship-1',
    'property_name' => 'static',
]);

#pr($dataset1->getContainerList());
#pr($dataset1->getRelatedProperties());
#pr($dataset1->foreign_container_collection->get('storeparam')->getSchema());
/* $custom_container_group = $dataset1->buildSelectedContainerGroup([
    'title',
    'name',
    'readparam',
    'storeparam',
]); */
#pr($custom_container_group->getKeys());
#var_dump($dataset1->getRelationshipNameForRelatedReadContainer('readparam'));;

$select_handle = $dataset1->getSelectHandle([
    'id',
    'title',
    'readparam',
    'relationship-1_my_date',
]);

#pr($select_handle->getExtrinsicContainerList());
$relationship = $select_handle->getRelationshipForExtrinsicContainer('readparam');
$perspective = $select_handle->getPerspectiveForExtrinsicContainer('readparam');
$the_other_dataset = $select_handle->getTheOtherDatasetForExtrinsicContainer('readparam');
#prl($the_other_dataset->getDatasetName());
#prl($select_handle->getPropertyNameForExtrinsicContainer('readparam'));

$fetch_manager = $dataset1->getFetchManager();
$data_server_context = $fetch_manager->list($select_handle);

foreach ($data_server_context as $model) {
    echo '#', $model->id,
    ' ', $model->relationship1_my_date,
    ' ', $model->title,
    ' ', $model->readparam,
    PHP_EOL;
}
