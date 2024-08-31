<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Properties\RelationalProperty;

// Required count group in cloned models.

/* Definition Collection Set */

$definition_array = [
    /* Shared Amount Groups */
    'group_1' => [
        'type' => 'group',
        'required_count' => 1,
        'description' => "",
    ],
    /* Group 2: Either url, filename, or blob (strictly just one). */
    'location_url' => [
        'type' => 'string',
        'required' => 'group_1',
        'description' => "Location URL.",
    ],
    'location_filename' => [
        'type' => 'string',
        'required' => 'group_1',
        'description' => "Location filename.",
    ],
    'location_blob' => [
        'type' => 'string',
        'required' => 'group_1',
        'description' => "Location blob.",
    ],
];

$definition_collection_set = DefinitionCollectionSet::fromArray(
    $definition_array
);

/* Model */

$relational_model = RelationalPropertyModel::fromDefinitionCollectionSet(
    $definition_collection_set
);

$relational_model_2 = clone $relational_model;

/* Properties */

// Just one should be enabled.
$relational_model_2->location_url = "https://www.example.com/";

$comparison = ($relational_model_2->getValuesWithMessages() === [
    'location_url' => [
        'value' => 'https://www.example.com/',
    ]
]);

Demo\assert_true(
    $comparison,
    "Model's meta data does not match the expected output"
);
