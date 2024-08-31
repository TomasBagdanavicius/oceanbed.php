<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link.php');
require_once(PROJECTS_PATH . '/data-project/src/private/Autoload.php');

use LWP\Common\Enums\AssortmentEnum;
use LWP\Components\Datasets\Relationships\RelationshipNodeKey;
use LWP\Components\Datasets\Relationships\Relationship;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Components\Datasets\Relationships\RelationshipNodeArrayStorage;
use LWP\Components\Datasets\Relationships\RelationshipNodeStorageInterface;
use LWP\Database\SyntaxBuilders\DatasetJoinSyntaxBuilder;

#todo: need a wrapper class around this to implement `RelationshipNodeStorageInterface`
$relationship_nodes_dataset = $database->getTable('relationship_nodes');

$lazy_datasets = [
    [
        'people',
        (static function () use ($database): DatasetInterface {
            return $database->getTable('people');
        }),
    ], [
        'countries',
        (static function () use ($database): DatasetInterface {
            return $database->getTable('countries');
        }),
    ],
];

#$relationship_node_array_storage = new RelationshipNodeArrayStorage([]);

#$relationship = new Relationship('people-to-countries', 1, $lazy_datasets, 1010000000, ['id', 'rel_column',]);
#$relationship = new Relationship('people-to-countries', 1, $lazy_datasets, 1010000000, ['id', 'id',], $relationship_nodes_dataset);
#$relationship = new Relationship('people-to-countries', 1, $lazy_datasets, 1010000000, ['custom_1', 'custom_2',], $database->getTable('test'));
$relationship = new Relationship('people-to-countries', 1, $lazy_datasets, 1010000000, ['country_residence', 'id',]);

$perspective = $relationship->getPerspectiveByContainerNumber(1);
$other_perspective = $relationship->getPerspectiveByContainerNumber(2);
$relationship_node_key = new RelationshipNodeKey('0.1');

$dataset_join_syntax_builder = new DatasetJoinSyntaxBuilder(
    perspective: $perspective,
    other_perspective: $other_perspective,
    node_key: $relationship_node_key,
    selection: AssortmentEnum::INCLUDE
);

#var_dump( $dataset_join_syntax_builder->getJoinPart() );
#var_dump( $dataset_join_syntax_builder->getOnPart() );
prl($dataset_join_syntax_builder->getFullString());
