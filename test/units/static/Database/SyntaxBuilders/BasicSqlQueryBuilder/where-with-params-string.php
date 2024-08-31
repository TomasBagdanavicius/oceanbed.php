<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link.php');

use LWP\Database\SyntaxBuilders\BasicSqlQueryBuilder;

$basic_sql_query_builder = (new BasicSqlQueryBuilder($sql_server))
    ->select("`column_name`")
    ->from('table1')
    ->where("`table1`.`name` = ?", params: ['foo'])
    ->where("`table1`.`key` = ?", params: [1]);

[$string, $params] = $basic_sql_query_builder->getFull();

Demo\assert_true(
    $string === "SELECT `column_name` FROM `table1` WHERE `table1`.`name` = ? AND `table1`.`key` = ?",
    "Unexpected result"
);
