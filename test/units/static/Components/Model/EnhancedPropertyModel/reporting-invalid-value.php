<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Model\EnhancedPropertyModel;
use LWP\Components\Definitions\DefinitionCollectionSet;

// Reporting invalid value.

$definition_collection_set = DefinitionCollectionSet::fromArray([
    'title' => [
        'type' => 'string',
        'max' => 10,
    ],
]);

$enhanced_property_model
    = EnhancedPropertyModel::fromDefinitionCollectionSet(
        $definition_collection_set
    );

$enhanced_property_model->setErrorHandlingMode(
    $enhanced_property_model::COLLECT_ERRORS
);
$enhanced_property_model->title = "Hello World!";

$values_with_messages = $enhanced_property_model->getValuesWithMessages(
    alt_values: true
);

Demo\assert_true(
    (
        isset($values_with_messages['title']['invalid_value'])
        && $values_with_messages['title']['invalid_value'] === "Hello World!"
    ),
    "Incorrect title structure"
);
