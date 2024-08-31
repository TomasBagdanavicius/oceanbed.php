<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Model\EnhancedPropertyModel;
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Violations\GenericViolation;

// Violation injection.

/* From Definition Collection Set */

$definition_collection_set = DefinitionCollectionSet::fromArray([
    'title' => [
        'type' => 'string',
        'default' => "Title",
    ],
]);

$enhanced_property_model
    = EnhancedPropertyModel::fromDefinitionCollectionSet(
        $definition_collection_set
    );

$enhanced_property_model->onBeforeSetValue(
    function (mixed $property_value): mixed {

        if ($property_value === "Hello World!") {

            $violation = new GenericViolation();
            $violation->setErrorMessageString("There was an error!");

            return $violation;
        }

        return $property_value;

    }
);

$enhanced_property_model->setErrorHandlingMode(
    $enhanced_property_model::COLLECT_ERRORS
);
$enhanced_property_model->title = "Hello World!";

Demo\assert_true(
    ($enhanced_property_model->getValuesWithMessages() === [
        'title' => [
            'errors' => [
                'There was an error!',
            ],
        ],
    ]),
    "Incorrect property value structure"
);
