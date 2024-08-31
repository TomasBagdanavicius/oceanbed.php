<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Conditions\Condition;

$definition_array = [
    'title' => [
        'type' => 'string',
        'max' => 255,
        'description' => "Main title.",
    ],
    'size' => [
        'type' => 'number',
        'max' => 100,
        'description' => "Shoe size.",
    ]
];

$definition_collection_set = DefinitionCollectionSet::fromArray($definition_array);

foreach ($definition_collection_set as $property_name => $definition_collection) {

    var_dump($property_name);

    foreach ($definition_collection as $definition_name => $definition) {
        var_dump($definition_name);
    }

    echo PHP_EOL;
}

/* Index Tree */

#pr( $definition_collection_set->getIndexableArrayCollection()->getIndexTree() );

/* Match Condition */

$condition = new Condition('type', 'string', ConditionComparisonOperatorsEnum::EQUAL_TO);

$filtered_collection = $definition_collection_set->matchCondition($condition);

prl("Found by matching condition: " . $filtered_collection->count());
print_r($filtered_collection->getKeys());
