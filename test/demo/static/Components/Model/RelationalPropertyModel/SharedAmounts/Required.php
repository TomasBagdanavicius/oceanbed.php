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

/* Definition Collection Set */

$definition_array = [
    /* Shared Amount Groups */
    /* 'group_1' => [
        'type' => 'group',
        'required_count' => 0,
        'description' => "At least one required group.",
    ], */
    'group_2' => [
        'type' => 'group',
        'required_count' => 1,
        'description' => "Just one required group.",
    ],
    /* Group 1: At least title or subtitle */
    /* 'title' => [
        'type' => 'string',
        'required' => 'group_1',
        // Should not affect the required state.
        'nullable' => true,
        'description' => "Main title.",
    ],
    'subtitle' => [
        'type' => 'string',
        'required' => 'group_1',
        // Should not affect the required state.
        'nullable' => true,
        'description' => "Subtitle.",
    ],
    'not-in-group' => [
        'type' => 'string',
        'description' => "Not in group property.",
    ], */
    /* Group 2: Either url, filename, or blob (strictly just one). */
    'location_url' => [
        'type' => 'string',
        'required' => 'group_2',
        // Should not affect the required state.
        'nullable' => true,
        'description' => "Location URL.",
    ],
    'location_filename' => [
        'type' => 'string',
        'required' => 'group_2',
        // Should not affect the required state.
        'nullable' => true,
        'description' => "Location filename.",
    ],
    'location_blob' => [
        'type' => 'string',
        'required' => 'group_2',
        // Should not affect the required state.
        'nullable' => true,
        'description' => "Location blob.",
    ],
    /* Closure value. */
    /* 'required_closure' => [
        'type' => 'string',
        // Full function declaration to showcase requirements.
        'required' => function( RelationalProperty $property ): bool {
            return true;
        },
        'description' => "Misc.",
    ], */
];

$definition_collection_set = DefinitionCollectionSet::fromArray($definition_array);

/* Model */

$relational_model = RelationalPropertyModel::fromDefinitionCollectionSet($definition_collection_set);

/* Properties */

// Either any one of the below two, or both can be enabled.
#$relational_model->title = "My Title";
#$relational_model->subtitle = "My Subtitle";

// Just one should be enabled.
$relational_model->location_url = "https://www.example.com/";
#$relational_model->location_filename = "/foo/bar/file.txt";
$relational_model->location_filename = null;
#$relational_model->location_blob = "blob:";

var_dump($relational_model->getValuesWithMessages(alt_values: true));
