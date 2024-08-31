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

// Full relation join in a cloned model.

$definition_collection_set = DefinitionCollectionSet::fromArray(
    $definition_array
);
$relational_model = RelationalPropertyModel::fromDefinitionCollectionSet(
    $definition_collection_set
);
$relational_model->setErrorHandlingMode($relational_model::COLLECT_ERRORS);

// Relational model is cloned.
$relational_model = clone $relational_model;

$relational_model->first_name = "John";
$relational_model->last_name = "Doe";

Demo\assert_true(
    $relational_model->full_name === "John Doe",
    "Unexpected joined full name value"
);
