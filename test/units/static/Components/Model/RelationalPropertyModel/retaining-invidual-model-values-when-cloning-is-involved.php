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

// Retaining invidual model values when cloning is involved.

$definition_collection_set = DefinitionCollectionSet::fromArray(
    $definition_array
);
$relational_model = RelationalPropertyModel::fromDefinitionCollectionSet(
    $definition_collection_set
);
$relational_model->setErrorHandlingMode(
    RelationalPropertyModel::COLLECT_ERRORS
);

// Before model is cloned.
$relational_model->title = "Title 1";

$relational_model_2 = clone $relational_model;
$relational_model_2->title = "Title 2";

Demo\assert_true(
    (
        // Each model has its own individual values.
        $relational_model->title === "Title 1"
        // Retains name that was built before its model was cloned.
        && $relational_model->name === "title-1"
        && $relational_model_2->title === "Title 2"
        && $relational_model_2->name === "title-2"
    ),
    "Incorrect property values"
);
