<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(__DIR__ . '/../../../../shared/definition-array.php');

use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Definitions\DefinitionCollectionSet;

// Tracking changes for properties with relations after cloning a data model.

$definition_collection_set = DefinitionCollectionSet::fromArray(
    $definition_array
);
$relational_model = RelationalPropertyModel::fromDefinitionCollectionSet(
    $definition_collection_set
);
$relational_model->setErrorHandlingMode(
    RelationalPropertyModel::COLLECT_ERRORS
);

$relational_model = clone $relational_model;

$relational_model->first_name = "John";

$relational_model->startTrackingChanges();

$relational_model->title = "My Title";
$relational_model->last_name = "Doe";

$changed_properties_array = $relational_model->stopTrackingChanges();

Demo\assert_true(
    $changed_properties_array === [
        'title' => "My Title",
        'name' => "my-title",
        'last_name' => "Doe",
        'full_name' => "John Doe",
    ],
    "Incorrect set of changed properties"
);
