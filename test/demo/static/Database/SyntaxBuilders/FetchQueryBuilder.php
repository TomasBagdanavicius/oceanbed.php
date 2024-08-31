<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

use LWP\Common\Enums\AssortmentEnum;
use LWP\Common\Enums\StandardOrderEnum;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Components\Datasets\Relationships\RelationshipNodeKey;
use LWP\Database\SyntaxBuilders\FetchQueryBuilder;

$dataset = $database->getTable('static');
$select_handle = $dataset->getSelectHandle();

$condition_1 = new Condition('id', '5', ConditionComparisonOperatorsEnum::LESS_THAN/*, data: [
    'abbreviation' => 'uri',
]*/);
$condition_2 = new Condition('date_last_modified', '2022-10-10 08:00:00', ConditionComparisonOperatorsEnum::GREATER_THAN);

$condition_group = new ConditionGroup();
$condition_group->add($condition_1);
$condition_group->add($condition_2, NamedOperatorsEnum::OR);

#prl( $condition_group->__toString() );

$fetch_query_builder = new FetchQueryBuilder(
    select_handle: $select_handle,
    model: $select_handle->getModel(),
    limit: null,
    offset: null,
    sort: null,
    order: StandardOrderEnum::ASC,
    filter_params: null,
    search_query: null,
    relationship_reference: null,
    selection: AssortmentEnum::INCLUDE,
    node_key: null,
    perspective_number: null,
    any_modules: null,
);

#pr( iterator_to_array($select_handle->yieldSelectExpressionMetadataList()) );
#eo($fetch_query_builder->getAsBasicSqlQueryBuilder()[0]);
#pr( $fetch_query_builder->getColumnListRelationships(exclude_global_relationship: false)->getKeys() );
#pr( iterator_to_array($fetch_query_builder->yieldJoinPartsForColumnListRelationships(exclude_global_relationship: false)) );
prl($fetch_query_builder->getAsBasicSqlQueryBuilder()[0]->getFullQueryString(format: true));
