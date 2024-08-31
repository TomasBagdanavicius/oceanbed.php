<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

use LWP\Database\MultiQueryManager;

$multi_query_manager = new MultiQueryManager($sql_server);

$multi_query_manager->add("SET @v1 = 'variable-1'", 'set-var-one', MultiQueryManager::RESULT_VOID);
$multi_query_manager->add("SET @v2 = 'variable-2'", 'set-var-two', MultiQueryManager::RESULT_VOID);
$multi_query_manager->add("SELECT @v1, @v2", 'get-vars', MultiQueryManager::RESULT_RESULT);
$multi_query_manager->add("CALL `_test`()", [
    'count' => MultiQueryManager::RESULT_RESULT,
    'temp' => MultiQueryManager::RESULT_RESULT,
    'test' => MultiQueryManager::RESULT_RESULT,
    'call' => MultiQueryManager::RESULT_VOID,
]);

$result_collection = $multi_query_manager->execute();
$result = $result_collection->get('count');

print_r($result->toArray());

/*

$single_query_manager = new SingleQueryManager("CALL test()", [
    'one' => RESULT_VOID,
    'two' => function( array $row ): string {
        return "Result";
    }
]);

$result_collection = $single_query_manager->getResults();

*/
