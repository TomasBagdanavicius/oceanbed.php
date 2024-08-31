<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Model\ModelDataIterator;
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Model\BasePropertyModel;

/* Definition Collection Set */

$definition_array = [
    'id' => [
        'type' => 'integer',
        'min' => 1,
        'unique' => true,
        'description' => "Primary",
    ],
    'name' => [
        'type' => 'string',
        'description' => "Name",
    ],
    'age' => [
        'type' => 'integer',
        'description' => "Age",
    ],
    'occupation' => [
        'type' => 'string',
        'description' => "Occupation",
    ],
    'height' => [
        'type' => 'number',
        'description' => "Height",
    ],
];

$definition_collection_set = DefinitionCollectionSet::fromArray($definition_array);

$base_model = BasePropertyModel::fromDefinitionCollectionSet($definition_collection_set);

/* Raw Data & Iterator */

$raw_data = [
    [
        'id' => 1,
        'name' => 'John',
        'age' => 35,
        'occupation' => 'Teacher',
        'height' => 1.92,
    ], [
        'id' => 2,
        'name' => 'Jane',
        'age' => 52,
        'occupation' => 'Lawyer',
        'height' => 1.71,
    ]
];

$array_object = new ArrayObject($raw_data);

/* Model Iterator */

$iterator = new ModelDataIterator($array_object, $base_model);

foreach ($iterator as $key => $model) {

    var_dump(
        $model->name,
        $model->age,
        $model->occupation,
        $model->height
    );
}
