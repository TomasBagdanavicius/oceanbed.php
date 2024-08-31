<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Model\Model;
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

$test_cases = [
    [
        'input' => [
            'first_color' => "White",
            'second_color' => "White",
        ],
        'expected_output' => [
            'second_color',
        ],
    ], [
        'input' => [
            'second_color' => "White",
            'first_color' => "White",
        ],
        'expected_output' => [
            'first_color',
        ],
    ], [
        'input' => [
            'first_color' => "White",
            'second_color' => "Black",
            'third_color' => "Black",
        ],
        'expected_output' => [
            'third_color',
        ],
    ], [
        'input' => [
            'first_color' => "White",
            'second_color' => "Black",
            'third_color' => "White",
        ],
        'expected_output' => [],
    ], [
        'input' => [
            'second_color' => "Black",
            'third_color' => "White",
            'first_color' => "White",
        ],
        'expected_output' => [],
    ],
];

$no_errors = true;
$error_message = '';

foreach ($test_cases as $key => $test_case) {

    $test_model = clone $relational_model;

    foreach ($test_case['input'] as $varname => $var_value) {
        $test_model->$varname = $var_value;
    }

    $result = $test_model->getValuesWithMessages();

    foreach ($result as $property_name => $result_value) {

        if (isset($result_value['errors']) && !in_array($property_name, $test_case['expected_output'])) {

            $no_errors = false;
            $error_message = sprintf(
                "Error in case %d: error was found in property %s, but it was not expected.",
                $key,
                $property_name
            );
            break 2;

        } elseif (!isset($result_value['errors']) && in_array($property_name, $test_case['expected_output'])) {

            $no_errors = false;
            $error_message = sprintf(
                "Error in case %d: error was not found in property %s.",
                $key,
                $property_name
            );
            break 2;
        }
    }
}

Demo\assert_true($no_errors, $error_message);
