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
    'first_name' => [
        'type' => 'string',
        'max' => 5,
        'description' => "First name.",
    ],
    'middle_name' => [
        'type' => 'string',
        'description' => "Middle name.",
    ],
    'last_name' => [
        'type' => 'string',
        'description' => "Last name.",
    ],
    'full_name' => [
        'type' => 'string',
        'join' => [
            'properties' => [
                'first_name',
                'middle_name',
                'last_name',
            ],
            'options' => [
                'separator' => ' ',
            ],
        ],
        'description' => "Full name.",
    ],
];

$definition_collection_set = DefinitionCollectionSet::fromArray($definition_array);

/* Model */

$model = RelationalPropertyModel::fromDefinitionCollectionSet($definition_collection_set);

/* Properties */

#$model->first_name = 'John';
#$model->middle_name = 'Secret';
#$model->last_name = 'Doe';
$model->full_name = 'John Secret Doe';

var_dump($model->full_name, $model->last_name);
var_dump($model->getValuesWithMessages());
