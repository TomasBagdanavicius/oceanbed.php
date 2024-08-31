<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link.php');

use LWP\Database\SyntaxBuilders\BasicSqlQueryBuilderWithExecution;

$basic_sql_query_builder = (new BasicSqlQueryBuilderWithExecution($sql_server))
    ->selectColumns(['id', 'short_title', 'iso_3166_1_alpha_2_code'])
    ->from('countries')
    ->where('`id` < 51')
    ->limit(row_count: 10, offset: 5);

prl($basic_sql_query_builder->getNoLimitCountQueryString());

/* Pager */

$pager = $basic_sql_query_builder->getPager(per_page: 15, current_page: 1);
echo "Page count: ";
var_dump($pager->getPageCount());
echo "Next page number: ";
var_dump($pager->getNextPageNumber());
