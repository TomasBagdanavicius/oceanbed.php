<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Model\RelationalPropertyModel;

/* Definition Collection Set */

$definition_array = [
    'first_color' => [
        'type' => 'string',
        'description' => "First color.",
    ],
    'second_color' => [
        'type' => 'string',
        'mismatch' => 'first_color',
        'description' => "Second color.",
    ],
    'third_color' => [
        'type' => 'string',
        'mismatch' => 'second_color',
        'description' => "Third color.",
    ],
];

$definition_collection_set = DefinitionCollectionSet::fromArray($definition_array);

/* Model */

$relational_model = RelationalPropertyModel::fromDefinitionCollectionSet($definition_collection_set);

/* Properties */

$relational_model->first_color = "White";
$relational_model->second_color = "Red";
$relational_model->third_color = "White";

var_dump($relational_model->getValuesWithMessages());
