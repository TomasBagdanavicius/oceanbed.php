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
    /* Properties */
    'title' => [
        'type' => 'string',
        'required' => 'group_1',
        'nullable' => true,
        'description' => "",
    ],
    'subtitle' => [
        'type' => 'string',
        'required' => 'group_1',
        'nullable' => true,
        'description' => "",
    ],
];

$definition_collection_set = DefinitionCollectionSet::fromArray(
    $definition_array
);

/* Model */

$relational_model = RelationalPropertyModel::fromDefinitionCollectionSet(
    $definition_collection_set
);

// This should not cancel group error
$relational_model->subtitle = null;

$comparison = ($relational_model->getValuesWithMessages() === [
    'title' => [
        'errors' => [
            'Either this or another value in group should be provided.',
        ],
    ],
    'subtitle' => [
        'value' => null,
        'errors' => [
            'Either this or another value in group should be provided.',
        ],
    ],
]);

Demo\assert_true(
    $comparison,
    "Model's meta data does not match the expected output"
);
