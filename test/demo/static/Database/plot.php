<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link.php');

use LWP\Database\Table;
use LWP\Components\Properties\Exceptions\PropertyValueContainsErrorsException;

$dataset = $database->getTable('countries');

$model = $dataset->getModel();
$dataset->getRelationalModelFromFullIntrinsicDefinitions(
    $model,
    field_value_extension: false,
    // This must be turned off when batch solution is used
    dataset_unique_constraint: false
);

$data = [
    'title' => "Democratic Republic of New Land",
    'name' => 'democratic-republic-of-new-land',
    'short_title' => "New Land",
    'iso_3166_1_alpha_2_code' => 'NL',
    'iso_3166_1_alpha_3_code' => 'DNL',
    'iso_3166_1_numeric_code' => '124',
    'zone_number' => 9,
];

foreach ($data as $property_name => $property_value) {

    try {
        $model->{$property_name} = $property_value;
    } catch (PropertyValueContainsErrorsException $exception) {
        prl($exception->getPrevious()->getMessage());
    }
}

$dataset->batchValidateUniqueContainers($model);

pr($model->getValuesWithMessages());
