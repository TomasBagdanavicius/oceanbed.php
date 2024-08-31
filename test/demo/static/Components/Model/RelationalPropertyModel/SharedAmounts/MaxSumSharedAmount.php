<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Definitions\DefinitionCollection;
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Model\EnhancedPropertyModel;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Properties\Exceptions\PropertyStateException;

$definition_array = [
    /* Group */
    'group_1' => [
        'type' => 'group',
        'max_sum' => 100, // <- Max sum
        'description' => "Maximum sum group.",
    ],
    'prop_1' => [
        'type' => 'integer',
        'groups' => [
            'group_1', // <- Group 1.
        ],
        'description' => "Property 1.",
    ],
    'prop_2' => [
        'type' => 'integer',
        'groups' => [
            'group_1', // <- Group 1.
        ],
        'description' => "Property 2.",
    ],
];

$definition_collection_set = DefinitionCollectionSet::fromArray($definition_array);
$relational_model = RelationalPropertyModel::fromDefinitionCollectionSet($definition_collection_set);
$relational_model->setErrorHandlingMode(EnhancedPropertyModel::THROW_ERROR_IMMEDIATELY);

try {

    $relational_model->prop_1 = 50;
    $relational_model->prop_2 = 70;
    #$relational_model->prop_2 = 40;

} catch (PropertyStateException $exception) {

    // Previous exception should be always available.
    prl(
        "Expected error ("
        . (Demo\DEBUGGER)->formatter->namespaceToIdeHtmlLink($exception::class)
        . "): "
        . $exception->getMessage()
        . " "
        . $exception->getPrevious()->getMessage()
    );
}

pr($relational_model->getValuesWithMessages());
