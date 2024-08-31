<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\{
    RelationalProperty,
    Exceptions\PropertyDependencyException
};
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Model\RelationalPropertyModel;

// From definition array.

$definition_array = [
    'title' => [
        'type' => 'string',
        'description' => "Main title.",
    ],
    'subtitle' => [
        'type' => 'string',
        'dependencies' => [
            'title',
        ],
        'description' => "Subtitle.",
    ],
];

$definition_collection_set = DefinitionCollectionSet::fromArray(
    $definition_array
);

$relational_model = RelationalPropertyModel::fromDefinitionCollectionSet(
    $definition_collection_set
);

$relational_model->setErrorHandlingMode(
    $relational_model::THROW_ERROR_IMMEDIATELY
);

// Park value due to dependency.
try {
    $relational_model->subtitle = "Subtitle";
} catch (PropertyDependencyException $exception) {
    // Continue.
}

$relational_model->title = "Title";

Demo\assert_true(
    (
        $relational_model->title === "Title"
        && $relational_model->subtitle === "Subtitle"
    ),
    "Incorrect property values"
);
