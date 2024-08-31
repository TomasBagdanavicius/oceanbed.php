<?php

declare(strict_types=1);

require_once(__DIR__ . '/../../database-link-test.php');

use LWP\Components\Datasets\Relationships\Relationship;
use LWP\Components\Datasets\Interfaces\DatasetInterface;

$dataset1 = $database->getTable('static');
$dataset2 = $database->getTable('test');
$lazy_datasets = [
    [
        'static',
        (static function () use ($dataset1): DatasetInterface {
            return $dataset1;
        }),
    ], [
        'test',
        (static function () use ($dataset2): DatasetInterface {
            return $dataset2;
        }),
    ]
];
$relationship = new Relationship(
    name: 'relationship-1',
    id: 1,
    dataset_array: $lazy_datasets,
    type_code: 1010000000,
    column_list: [
        'id',
        'static',
    ]
);
$database->setRelationship($relationship);
