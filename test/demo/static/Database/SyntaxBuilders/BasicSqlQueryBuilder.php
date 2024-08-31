<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link.php');

use LWP\Database\SyntaxBuilders\BasicSqlQueryBuilder;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Common\Enums\StandardOrderEnum;
use LWP\Database\SyntaxBuilders\Enums\BasicSqlQueryTypesEnum;

$basic_sql_query_builder = (new BasicSqlQueryBuilder($sql_server))

    /* Select */

    // Single expression with alias name.
    ->select("1 + 1", 'math_1')
    // Multiple expressions.
    ->select([
        ["'x' IN ('a','b','c')", 'in_abc'],
        ["COALESCE(NULL,1)", 'first'],
    ])
    // Column.
    ->selectColumn('column_name', 't1', 'alias_1')
    // Prefixed column parsing.
    ->selectColumn('t2.column_name', true, 'alias_2')
    // All symbol.
    ->selectColumn(BasicSqlQueryBuilder::ALL_SYMBOL, 't4')
    // Multiple columns.
    ->selectColumns(['id', ['date_created'], ['name', 'alias_3'], ['col', 'alias_4', 't2']], 't1')
    // From definitions.
    ->selectFromMetadata([
        [
            'expression' => '2 + 2',
            'alias_name' => 'math_2',
        ], [
            'column' => 'col1',
            'table_reference' => 't1',
            'alias_name' => 'alias',
        ],
    ])

    /* From */

    ->from(['t1', ['t2'], ['table3', 't3']])
    ->from('table4', 't4')

    /* Other */

    ->join("JOIN `t1` ON `t2`.`id` = `t1`.`id`")
    ->join("LEFT JOIN `t4` ON `t4`.`id` = `t1`.`id`")
    ->where("`t1`.`name` = 'test'")
    ->orderBy('`t1`.`id`')
    ->orderBy('`t1`.`col1`', StandardOrderEnum::DESC)
    ->groupBy('`t1`.`id`')
    ->limit(row_count: 10, offset: 5);

/* Adding In Conditions */

$condition_data = [
    // To add table prefix.
    'table' => 't1',
];

$condition_1 = new Condition('id', 1, ConditionComparisonOperatorsEnum::LESS_THAN, $condition_data);
$condition_2 = new Condition('name', 'test', ConditionComparisonOperatorsEnum::NOT_EQUAL_TO, $condition_data);
$condition_3 = new Condition('level', 3, ConditionComparisonOperatorsEnum::GREATER_THAN, $condition_data);
$condition_4 = new Condition('date', '2022-01-01 08:00:00', ConditionComparisonOperatorsEnum::LESS_THAN, $condition_data);
$condition_5 = new Condition('col', 'a', ConditionComparisonOperatorsEnum::CONTAINS, $condition_data);

$condition_group_1 = new ConditionGroup();
$condition_group_1->add($condition_3);
$condition_group_1->add($condition_4, NamedOperatorsEnum::OR);

$basic_sql_query_builder
    ->whereCondition($condition_1)
    ->whereCondition($condition_2, NamedOperatorsEnum::OR)
    ->whereCondition($condition_group_1, NamedOperatorsEnum::AND)
    ->whereCondition($condition_5, NamedOperatorsEnum::AND);

/* Result String */

prl($basic_sql_query_builder->getFullQueryString(BasicSqlQueryTypesEnum::FULL, format: true));
#prl($basic_sql_query_builder->getNoLimitCountQueryString(format: true));

/* Debugging */

#pre($basic_sql_query_builder->getSelectParts());
