<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link.php');

use LWP\Database\SyntaxBuilders\BasicSqlQueryBuilder;
use LWP\Common\Enums\StandardOrderEnum;

$basic_sql_query_builder = (new BasicSqlQueryBuilder($sql_server))
    ->select("`column_name`")
    ->from('table1')
    ->orderBy('CASE WHEN `column1` = ? THEN 1 WHEN `column1` = ? THEN 2 ELSE 3 END', StandardOrderEnum::DESC, params: ['foo', 1]);

[$string, $params] = $basic_sql_query_builder->getFull();

Demo\assert_true(
    $params === [
        'foo',
        1
    ],
    "Unexpected result"
);
